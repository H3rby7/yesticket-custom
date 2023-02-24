<?php

include_once("event_using_shortcode.php");
include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

add_shortcode('yesticket_events', 'ytp_shortcode_events');

/**
 * Callback to add_shortcode [yesticket_events]
 */
function ytp_shortcode_events($atts)
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
        'details' => 'no',
        'grep' => NULL,
    ), $atts);
    return YesTicketEvents::getInstance()->get($att);
}

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

    protected $cssClass = 'ytp-events';
    public function __construct()
    {
        parent::__construct();
    }

    function render_contents($result, $att)
    {
        $content = "";
        foreach ($result as $item) {
            $content .= $this->render_single_event($item, $att);
        }
        return $content;
    }

    /**
     * Return an event as html
     * 
     * @param object $item one YesTicket Event
     * @param array $att shortcode attributes
     * 
     * @return string html for the event
     */
    private function render_single_event($item, $att)
    {
        $event_id = 'ytp-event-' . $item->event_id;
        $booking_url = $item->yesticket_booking_url;
        $ticket_text = __("Tickets", "yesticket");
        $event_datetime = ytp_render_date_and_time($item->event_datetime);
        $event_name = htmlentities($item->event_name);
        $location_name = htmlentities($item->location_name);
        $location_city = htmlentities($item->location_city);
        $event_type = "";
        if ($att["type"] == "all") {
            $event_type = "<span class='ytp-event-type'>" . ytp_render_eventType($item->event_type) . "</span>";
        }
        $urgency = "";
        if (!empty($item->event_urgency_string)) {
            $urgency = "<span class='ytp-event-urgency'>" . htmlentities($item->event_urgency_string) . "</span>";
        }
        $details = "";
        if ($att["details"] == "yes") {
            $details = $this->render_event_details($item);
        }
        return <<<EOD
        <div class='ytp-event-row' id="$event_id">
            <div class='ytp-event-info'>
                <h3 class='ytp-event-name'>$event_name $event_type</h3>
                <span class='ytp-event-location'>$location_name</span>
                <span class='ytp-event-city'>$location_city</span>
                <span class='ytp-event-date'>$event_datetime</span>
                <div>$urgency</div>
                <div class='ytp-event-details'>
                    $details
                </div>
            </div>
            <div class='ytp-event-ticket'>
                <a href="'.$booking_url.'" target="_blank" class="ytp-button">$ticket_text</a>
            </div>
        </div>
EOD;
        // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
    }

    /**
     * Return the details of an event as html
     * 
     * @param object $item one YesTicket Event
     * 
     * @return string html for the details of this event
     */
    private function render_event_details($item)
    {
        $event_id = 'ytp-event-' . $item->event_id;
        $event_description = nl2br(htmlentities($item->event_description));
        $show_details_text = __("Show details", "yesticket");
        $hints_heading = __("Hints", "yesticket");
        $hints_text = nl2br(htmlentities($item->event_notes_help));
        $tickets_heading = __("Tickets", "yesticket");
        $ticket_text = htmlentities($item->tickets);
        $location_heading = __("Location", "yesticket");
        $location_info = $this->render_details_location_info($item);
        $back_to_top_text = __("Back to top", "yesticket");
        return <<<EOD
        <details class="ytp-event-details-details">
            <summary class="ytp-event-details-summary">$show_details_text</summary>
            <div>
                $event_description
                <h5>$hints_heading</h5>
                $hints_text
                <h5>$tickets_heading</h5>
                $ticket_text
                <h5>$location_heading</h5>
                $location_info
            </div>
            <a class="ytp-event-details-back-to-top" href="#$event_id">$back_to_top_text</a>
        </details>
EOD;
        // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
    }

    /**
     * Return the location details of an event as html
     * 
     * @param object $item one YesTicket Event
     * 
     * @return string html for the location details of this event
     */
    private function render_details_location_info($item)
    {
        $name = htmlentities($item->location_name); //br
        $street = htmlentities($item->location_street); //br
        $zip = htmlentities($item->location_zip); //
        $city = htmlentities($item->location_city); //,
        $state = htmlentities($item->location_state); //,
        $country = htmlentities($item->location_country);
        // TODO: a case for translation actually, to catch country specific formatting!
        return <<<EOD
        <div class="ytp-event-details-location">
            <span class="ytp-event-details-location-name">$name</span>
            <span class="ytp-event-details-location-street">$street</span>
            <span class="ytp-event-details-location-zip">$zip</span>
            <span class="ytp-event-details-location-city">$city</span>
            <span class="ytp-event-details-location-state">$state</span>
            <span class="ytp-event-details-location-country">$country</span>
        </div>
EOD;
        // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
    }
}
