<?php

namespace YesTicket;

include_once("event_using_shortcode.php");
include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

\add_shortcode('yesticket_events', array('YesTicket\Events', 'shortCode'));

/**
 * Shortcode [yesticket_events]
 */
class Events extends EventUsingShortcode
{
  /**
   * The $instance
   * @var Events
   */
  static private $instance;
  static public function getInstance()
  {
    if (!isset(Events::$instance)) {
      Events::$instance = new Events(Api::getInstance());
    }
    return Events::$instance;
  }
  static public function shortCode($atts)
  {
    return Events::getInstance()->get($atts);
  }

  protected $cssClass = 'ytp-events';
  public function __construct($api)
  {
    parent::__construct($api);
  }

  protected function shortCodeArgs($atts)
  {
    $pairs = parent::shortCodeArgs($atts);
    $pairs['details'] = 'no';
    return \shortcode_atts($pairs, $atts, 'yesticket_events');
  }

  function render_contents($result, $att)
  {
    $content = "";
    foreach ($result as $item) {
      $content .= $this->render_template('event_row', \compact("item", "att"));
    }
    return $content;
  }

  function render_event_id($item)
  {
    echo 'ytp-event-' . $item->event_id;
  }
}
