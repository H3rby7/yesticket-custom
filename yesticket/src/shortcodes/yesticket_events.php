<?php

namespace YesTicket;

include_once("event_using_shortcode.php");
include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

add_shortcode('yesticket_events', array('YesTicket\Events', 'shortCode'));

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
            Events::$instance = new Events();
        }
        return Events::$instance;
    }
    static public function shortCode($atts)
    {
        return Events::getInstance()->get($atts);
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
