<?php

include_once("event_using_shortcode.php");
include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

add_shortcode('yesticket_events', array('YesTicketEvents', 'shortCode'));

/**
 * Shortcode [yesticket_events]
 */
class YesTicketEvents extends YesTicketEventUsingShortcode
{
    /**
     * The $instance
     * @var YesTicketEvents
     */
    static private $instance;
    static public function getInstance()
    {
        if (!isset(YesTicketEvents::$instance)) {
            YesTicketEvents::$instance = new YesTicketEvents();
        }
        return YesTicketEvents::$instance;
    }
    static public function shortCode($atts)
    {
        return YesTicketEvents::getInstance()->get($atts);
    }

    protected $cssClass = 'ytp-events';
    public function __construct()
    {
        parent::__construct();
    }

    function render_contents($result, $att)
    {
        $content = "";
        foreach ($result as $item) {
            $content .= $this->render_template('event_row', compact("item", "att"));
        }
        return $content;
    }

    function render_event_id($item)
    {
        echo 'ytp-event-' . $item->event_id;
    }
}
