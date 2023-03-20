<?php

namespace YesTicket;

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

\add_shortcode('yesticket_events_cards', array('YesTicket\EventsCards', 'shortCode'));

/**
 * Shortcode [yesticket_events_cards]
 */
class EventsCards extends EventUsingShortcode
{
  /**
   * The $instance
   * @var EventsCards
   */
  static private $instance;
  static public function getInstance()
  {
    if (!isset(EventsCards::$instance)) {
      EventsCards::$instance = new EventsCards(Api::getInstance());
    }
    return EventsCards::$instance;
  }
  static public function shortCode($atts)
  {
    return EventsCards::getInstance()->get($atts);
  }

  protected $cssClass = 'ytp-event-cards';
  public function __construct($api)
  {
    parent::__construct($api);
  }

  protected function shortCodeArgs($atts)
  {
    $pairs = parent::shortCodeArgs($atts);
    $pairs['count'] = 9;
    return \shortcode_atts($pairs, $atts, 'yesticket_events_cards');
  }

  function render_contents($result, $att)
  {
    $content = "";
    foreach ($result as $item) {
      if (!empty($att["grep"])) {
        if (\mb_stripos($item->event_name, $att["grep"]) === FALSE) {
          // Did not find the required Substring in the event_title, skip this event
          continue;
        }
      }
      $content .= $this->render_template('event_card', \compact("item"));
    }
    if (empty($content)) {
      // content could be empty, if everything is filtered by 'grep'
      $content = \ytp_render_no_events();
    }
    return $content;
  }
}
