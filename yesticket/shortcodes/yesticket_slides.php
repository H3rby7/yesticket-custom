<?php

include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ . "/../yesticket_helpers.php");
include_once(__DIR__ . "/../yesticket_api.php");

add_shortcode('yesticket_slides', 'ytp_shortcode_slides');

function ytp_shortcode_slides($atts)
{
  $att = shortcode_atts(array(
    'type' => 'all',
    'env' => 'prod',
    'count' => '10',
    'teaser-length' => '250',
    'ms-per-slide' => '10000',
    'text-scale' => '100%',
    'color-1' => '#ffffff',
    'color-2' => '#000000',
    'welcome-1' => __('welcome to our', "yesticket"),
    'welcome-2' => __('improv theatre show', "yesticket"),
    'welcome-3' => __('where everything is made up', "yesticket"),
  ), $atts);
  return YesTicketSlides::getInstance()->get($att);
}

class YesTicketSlides
{
  static private $instance;
  static public function getInstance()
  {
    if (!isset(YesTicketSlides::$instance)) {
      YesTicketSlides::$instance = new YesTicketSlides(__DIR__ . '/webslides');
    }
    return YesTicketSlides::$instance;
  }

  /**
   * Path to the example templates.
   *
   * @var string
   */
  protected $template_path;

  /**
   * Constructor.
   *
   * @param string $template_path
   */
  public function __construct($template_path)
  {
    $this->template_path = rtrim($template_path, '/');
    add_action('wp_enqueue_scripts', [$this, 'getStyles']);
  }

  public function getStyles()
  {
    wp_enqueue_style('yesticket_slides', plugins_url('webslides/webslides.css', __FILE__), false, 'all');
    wp_enqueue_script('yesticket_slides', plugins_url('webslides/webslides.min.js', __FILE__));
  }

  public function get($att)
  {
    $content = "";
    try {
      $result = YesTicketApi::getInstance()->getEvents($att);
      $content .= $this->inlineStyles($att);
      $content .= "<div id='ytp-slides' style='font-size: " . $att["text-scale"] . "'>";
      if (!is_countable($result) or count($result) < 1) {
        $content .= ytp_render_no_events();
      } else if (array_key_exists('message', $result) && $result->message == "no items found") {
        $content .= ytp_render_no_events();
      } else {
        $content .= $this->render_slides($result, $att);
      }
    } catch (Exception $e) {
      $content .= __($e->getMessage(), 'yesticket');
    }
    $content .= "</div>";
    return $content;
  }

  function inlineStyles($att)
  {
    $color1 = $att['color-1'];
    $color2 = $att['color-2'];
    return <<<EOD
      <style>
        #ytp-slides {
          --ytp--color--primary: $color1;
          --ytp--color--contrast: $color2;
        }
      </style>
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
  }

  function render_slides($result, $att)
  {
    $content = $this->render_template("slides_header", compact("att"));
    $count = 0;
    foreach ($result as $event) {
      $content .= $this->render_template("slide_event", compact("event", "att"));
      $count++;
      if ($count == (int)$att["count"]) {
        break;
      }
    }
    $content .= $this->render_template("slides_footer", compact("att"));
    return $content;
  }

  function dateAndTime($dateTimeString)
  {
    echo ytp_render_date_and_time($dateTimeString);
  }

  function eventDescription($item, $att)
  {
    $descr = $item->event_description;
    if (strlen($descr) < $att["teaser-length"]) {
      return $descr;
    }
    $shorter = substr($descr, 0, $att["teaser-length"]);
    $indexOfLastPunctuationMark = strpos_findLast_viaRegex($shorter, "/[!.?]/i");
    if (!$indexOfLastPunctuationMark) {
      return $shorter . "[...]";
    } else {
      return substr($shorter, 0, $indexOfLastPunctuationMark + 1);
    }
  }

  /**
   * Renders the given template if it's readable.
   *
   * @param string $template
   */
  function render_template($template, $variables = array())
  {
    $template_path = $this->template_path . '/' . $template . '.php';

    if (!is_readable($template_path)) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": 'Template not found: $template_path'");
      return;
    }
    // Extract the variables to a local namespace
    extract($variables);

    ob_start();
    include $template_path;
    $result = ob_get_clean();
    return $result;
  }
}
