<?php

namespace YesTicket;

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

\add_shortcode('yesticket_events_list', array('YesTicket\EventsList', 'shortCode'));

/**
 * Shortcode [yesticket_events_list]
 */
class EventsList extends EventUsingShortcode
{
  /**
   * The $instance
   * @var EventsList
   */
  static private $instance;
  static public function getInstance()
  {
    if (!isset(EventsList::$instance)) {
      EventsList::$instance = new EventsList(Api::getInstance());
    }
    return EventsList::$instance;
  }
  static public function shortCode($atts)
  {
    return EventsList::getInstance()->get($atts);
  }

  protected $cssClass = 'ytp-event-list';
  public function __construct($api)
  {
    parent::__construct($api);
  }

  protected function shortCodeArgs($atts)
  {
    $pairs = parent::shortCodeArgs($atts);
    $pairs['ticketlink'] = 'no';
    return \shortcode_atts($pairs, $atts, 'yesticket_events_list');
  }

  function render_contents($result, $att)
  {
    $content = "<ol>\n";
    foreach ($result as $item) {
      $content .= $this->render_template('event_list_item', \compact("item", "att"));
    }
    $content .= "</ol>\n";
    return $content;
  }
}
