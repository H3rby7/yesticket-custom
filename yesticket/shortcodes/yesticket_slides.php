<?php

include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");



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
        $content .= "<div id='ytp-slides' style='font-size: ".$att["text-scale"]."'>";
        if (!is_countable($result) or count($result) < 1) {
            $content = ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content = ytp_render_no_events();
        } else {
            $content .= $this->render_slides($result, $att);
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
  }

  function inlineStyles($att) {
  $color1 = $att['color-1'];
  $color2 = $att['color-2'];
  return <<<EOD
  <style>
    #ytp-slides {
      --ytp--color--primary: $color1;
      --ytp--color--contrast: $color2;
    }
  </style>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  function render_slides($result, $att) {
    $content = <<<EOD
    <main role="main">
      <article id="webslides">
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
    $content .= $this->welcomeSlide($att["welcome-1"], $att["welcome-2"], $att["welcome-3"]);
    $count = 0;
    foreach ($result as $item) {
      $content .= $this->eventSlide($item, $att);
      $count++;
      if ($count == (int)$att["count"]) {
          break;
      }
    }
    $content .= <<<EOD
      </article>
    </main>
    <!--main-->
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
    $autoslide = $att["ms-per-slide"];
    $content .= $this->webslidesJS($autoslide);
    return $content;
  }
  
  function welcomeSlide($row1, $row2, $row3) {
  return <<<EOD
        <section class="">
          <div class="wrap aligncenter slow">
            <p class="text-symbols">$row1</p>
            <h1 class="text-landing">$row2</h1>
            <p class="text-symbols">$row3</p>
          </div>
        </section>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function webslidesJS($autoslide) {
  return <<<EOD
  <script>
  window.addEventListener('load', function () {
    window.ws = new WebSlides(
      { autoslide: $autoslide }
    );
  }, false);
  </script>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function eventSlide($event, $att) {
    $bg_image_url = $event->event_picture_url;
    $event_name = $event->event_name;
    $date_and_location = ytp_render_date_and_time($event->event_datetime) . ", " . $event->location_name;
    $description = $this->eventDescription($event, $att);
    return <<<EOD
    <section class="yesticket-slide">
      <span class="background fadeIn" style="background-image:url('$bg_image_url')"></span>
      <div class="wrap">
        <div class="yesticket-event-meta slide-top slideInLeft delay">
          <h2 class="yesticket-event-name">$event_name</h2>
          <p>$date_and_location</p>
          <div class="backdrop-dark"><div></div></div>
        </div>
        <div class="yesticket-event-teaser slideInRight delay delay2">
          <p>$description</p>
          <div class="backdrop-dark"><div></div></div>
        </div>
      </div>
      <!-- end .yesticket-slide-->
    </section>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function eventDescription($item, $att) {
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
}
