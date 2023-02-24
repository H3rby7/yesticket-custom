<?php

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

add_shortcode('yesticket_events_list', array('YesTicketEventsList', 'shortCode'));

/**
 * Shortcode [yesticket_events_list]
 */
class YesTicketEventsList extends YesTicketEventUsingShortcode
{
    /**
     * The $instance
     * @var YesTicketEventsList
     */
    static private $instance;
    static public function getInstance()
    {
        if (!isset(YesTicketEventsList::$instance)) {
            YesTicketEventsList::$instance = new YesTicketEventsList();
        }
        return YesTicketEventsList::$instance;
    }
    static public function shortCode($atts)
    {
        return YesTicketEventsList::getInstance()->get($atts);
    }

    protected $cssClass = 'ytp-event-list';
    public function __construct()
    {
        parent::__construct();
    }

    function render_contents($result, $att)
    {
        $content = "<ol>\n";
        foreach ($result as $item) {
            $content .= $this->render_template('event_list_item', compact("item", "att"));
        }
        $content .= "</ol>\n";
        return $content;
    }
}
