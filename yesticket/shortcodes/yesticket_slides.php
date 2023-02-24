<?php

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");
include_once(__DIR__ . "/../helpers/templater.php");

add_shortcode('yesticket_slides', array('YesTicketSlides', 'shortCode'));

class YesTicketSlides extends YesTicketEventUsingShortcode
{
  static private $instance;
  static public function getInstance()
  {
    if (!isset(YesTicketSlides::$instance)) {
      YesTicketSlides::$instance = new YesTicketSlides();
    }
    return YesTicketSlides::$instance;
  }
  static public function shortCode($atts)
  {
    wp_enqueue_style('yesticket_slides');
    wp_enqueue_script('yesticket_slides');
    return YesTicketSlides::getInstance()->get($atts);
  }

  /**
   * Register needed CSS and JS files with wordpress using wp_register_xxxx
   * Call this in the plugin init
   */
  static public function registerFiles()
  {
    wp_register_style('yesticket_slides', plugins_url('webslides/webslides.css', __FILE__), false, 'all');
    wp_register_script('yesticket_slides', plugins_url('webslides/webslides.min.js', __FILE__), false, 'all');
  }

  protected function shortCodeArgs($atts)
  {
    return shortcode_atts(array(
      'type' => 'all',
      'env' => 'prod',
      'count' => '10',
      'grep' => NULL,
      'teaser-length' => '250',
      'ms-per-slide' => '10000',
      'text-scale' => '100%',
      'color-1' => '#ffffff',
      'color-2' => '#000000',
      'welcome-1' => __('welcome to our', "yesticket"),
      'welcome-2' => __('improv theatre show', "yesticket"),
      'welcome-3' => __('where everything is made up', "yesticket"),
    ), $atts);
  }

  public function __construct()
  {
    parent::__construct();
  }

  function render_contents($result, $att)
  {
    return $this->render_template("slides", compact("result", "att"));
  }

  /**
   * Print the (shortened) event description
   * @param mixed $item the event
   * @param array $att shortcode attributes
   */
  function eventDescription($item, $att)
  {
    $descr = $item->event_description;
    if (strlen($descr) < $att["teaser-length"]) {
      print $descr;
      return;
    }
    $shorter = substr($descr, 0, $att["teaser-length"]);
    $indexOfLastPunctuationMark = strpos_findLast_viaRegex($shorter, "/[!.?]/i");
    if (!$indexOfLastPunctuationMark) {
      print $shorter . "[...]";
      return;
    } else {
      print substr($shorter, 0, $indexOfLastPunctuationMark + 1);
      return;
    }
  }
}
