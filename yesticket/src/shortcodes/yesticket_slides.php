<?php

namespace YesTicket;

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");
include_once(__DIR__ . "/../helpers/templater.php");

\add_shortcode('yesticket_slides', array('YesTicket\Slides', 'shortCode'));

class Slides extends EventUsingShortcode
{
  static private $instance;
  /**
   * @return Slides
   */
  static public function getInstance()
  {
    if (!isset(Slides::$instance)) {
      Slides::$instance = new Slides(Api::getInstance());
    }
    return Slides::$instance;
  }
  static public function shortCode($atts)
  {
    \wp_enqueue_style('yesticket_slides');
    \wp_enqueue_script('yesticket_slides');
    return Slides::getInstance()->get($atts);
  }

  /**
   * Register needed CSS and JS files with wordpress using wp_register_xxxx
   * Call this in the plugin init
   */
  static public function registerFiles()
  {
    $pathToSlidesCss = \plugins_url('webslides/webslides.css', __FILE__);
    $pathToSlidesJs = \plugins_url('webslides/webslides.min.js', __FILE__);
    if (!\wp_register_style('yesticket_slides', $pathToSlidesCss, false, 'all')) {
      \ytp_info(__FILE__, __LINE__, "Could not register_style: 'yesticket_slides' from '$pathToSlidesCss'.");
    }
    if (!\wp_register_script('yesticket_slides', $pathToSlidesJs, false, 'all')) {
      \ytp_info(__FILE__, __LINE__, "Could not register_script: 'yesticket_slides' from '$pathToSlidesJs'.");
    }
  }

  protected function shortCodeArgs($atts)
  {
    return \shortcode_atts(array(
      'type' => 'all',
      'env' => 'prod',
      'count' => 100,
      'grep' => NULL,
      'teaser-length' => '250',
      'ms-per-slide' => '10000',
      'text-scale' => '100%',
      'color-1' => 'white',
      'color-2' => 'black',
      'welcome-1' => __('welcome to our', "yesticket"),
      'welcome-2' => __('improv theatre show', "yesticket"),
      'welcome-3' => __('where everything is made up', "yesticket"),
    ), $atts, 'yesticket_slides');
  }

  protected $cssClass = 'ytp-slides';
  public function __construct($api)
  {
    parent::__construct($api);
  }

  function render_contents($result, $att)
  {
    return $this->render_template("slides", \compact("result", "att"));
  }

  /**
   * Print the (shortened) event description
   * @param mixed $item the event
   * @param array $att shortcode attributes
   */
  public function print_eventDescription($item, $att)
  {
    $descr = $item->event_description;
    if (\strlen($descr) < $att["teaser-length"]) {
      print $descr;
      return;
    }
    $shorter = \substr($descr, 0, $att["teaser-length"]);
    $indexOfLastPunctuationMark = \strpos_findLast_viaRegex($shorter, "/[!.?]/i");
    if (!$indexOfLastPunctuationMark) {
      print $shorter . "[...]";
      return;
    } else {
      print \substr($shorter, 0, $indexOfLastPunctuationMark + 1);
      return;
    }
  }
}
