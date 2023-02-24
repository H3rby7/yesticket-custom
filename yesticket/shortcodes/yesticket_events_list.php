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
     * @var YesTicketEvents
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
            $content .= $this->render_list_item($item, $att);
        }
        $content .= "</ol>\n";
        return $content;
    }

    /**
     * Return an event as html list item
     * 
     * @param object $item one YesTicket Event
     * 
     * @return string html for the event as list item
     */
    private function render_list_item($item, $att)
    {
        $event_date = ytp_render_date($item->event_datetime);
        $event_time = ytp_render_time($item->event_datetime);
        $event_name = htmlentities($item->event_name);
        $location_name = htmlentities($item->location_name);
        $location_city = htmlentities($item->location_city);
        $event_type = "";
        if ($att["type"] == "all") {
            $event_type = "<li class='ytp-event-list-type'>" . ytp_render_eventType($item->event_type) . "</li>";
        }
        $booking = "";
        if ($att["ticketlink"] == "yes") {
            $booking .= '<li class="ytp-event-list-tickets"><a href="' . $item->yesticket_booking_url . '" target="_blank">Tickets</a></li>';
        }
        return <<<EOD
        <li class='ytp-event-list-row'>
            <ul>
                <li class='ytp-event-list-date'>$event_date</li>
                <li class='ytp-event-list-time'>$event_time</li>
                $event_type
                <li class='ytp-event-list-name'>$event_name</li>
                <li class='ytp-event-list-location'>$location_name</li>
                <li class='ytp-event-list-city'>$location_city</li>
                $booking
            </ul>
        </li>
EOD;
        // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
    }
}
