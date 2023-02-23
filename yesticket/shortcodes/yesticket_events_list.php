<?php

include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ . "/../yesticket_helpers.php");
include_once(__DIR__ . "/../yesticket_api.php");

add_shortcode('yesticket_events_list', 'ytp_shortcode_events_list');

/**
 * Callback to add_shortcode [yesticket_events_list]
 */
function ytp_shortcode_events_list($atts)
{
    wp_enqueue_style('yesticket');
    $att = shortcode_atts(array(
        'env' => NULL,
        'api-version' => NULL,
        'organizer' => NULL,
        'key' => NULL,
        'type' => 'all',
        'count' => 9,
        'theme' => 'light',
        'ticketlink' => 'no',
        'grep' => NULL,
    ), $atts);
    return YesTicketEventsList::getInstance()->get($att);
}

/**
 * Shortcode [yesticket_events_list]
 */
class YesTicketEventsList
{
    /**
     * The $instance
     *
     * @var YesTicketEventsList
     */
    static private $instance;

    /**
     * Get the $instance
     * 
     * @return YesTicketEventsList $instance
     */
    static public function getInstance()
    {
        if (!isset(YesTicketEventsList::$instance)) {
            YesTicketEventsList::$instance = new YesTicketEventsList();
        }
        return YesTicketEventsList::$instance;
    }

    /**
     * Return the rendered shortcode content as html elements
     * 
     * @param array $att shortcode attributes
     * 
     * @return string shortcode content
     */
    public function get($att)
    {
        $content = ytp_render_shortcode_container_div("ytp-event-list", $att);
        try {
            $result = YesTicketApi::getInstance()->getEvents($att);
            if (!is_countable($result) or count($result) < 1) {
                $content .= ytp_render_no_events();
            } else if (array_key_exists('message', $result) && $result->message == "no items found") {
                $content .= ytp_render_no_events();
            } else {
                $content .= $this->render_list($result, $att);
            }
            //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        } catch (Exception $e) {
            $content .= __($e->getMessage(), 'yesticket');
        }
        $content .= "</div>\n";
        return $content;
    }

    /**
     * Return the events as html list
     * 
     * @param array $result of the YesTicket API call for events
     * @param array $att shortcode attributes
     * 
     * @return string html for the events as list
     */
    private function render_list($result, $att)
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
