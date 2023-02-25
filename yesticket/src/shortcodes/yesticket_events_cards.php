<?php

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

add_shortcode('yesticket_events_cards', array('YesTicketEventsCards', 'shortCode'));

/**
 * Shortcode [yesticket_events_cards]
 */
class YesTicketEventsCards extends YesTicketEventUsingShortcode
{
    /**
     * The $instance
     * @var YesTicketEventsCards
     */
    static private $instance;
    static public function getInstance()
    {
        if (!isset(YesTicketEventsCards::$instance)) {
            YesTicketEventsCards::$instance = new YesTicketEventsCards();
        }
        return YesTicketEventsCards::$instance;
    }
    static public function shortCode($atts)
    {
        return YesTicketEventsCards::getInstance()->get($atts);
    }

    protected $cssClass = 'ytp-event-cards';
    public function __construct()
    {
        parent::__construct();
    }

    function render_contents($result, $att)
    {
        $content = "";
        foreach ($result as $item) {
            if (!empty($att["grep"])) {
                if (mb_stripos($item->event_name, $att["grep"]) === FALSE) {
                    // Did not find the required Substring in the event_title, skip this event
                    continue;
                }
            }
            $content .= $this->render_template('event_card', compact("item"));
        }
        if (empty($content)) {
            // content could be empty, if everything is filtered by 'grep'
            $content = ytp_render_no_events();
        }
        return $content;
    }
}
