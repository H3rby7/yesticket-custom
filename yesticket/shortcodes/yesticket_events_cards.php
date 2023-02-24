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
            $content .= $this->render_single_card($item);
        }
        if (empty($content)) {
            // content could be empty, if everything is filtered by 'grep'
            $content = ytp_render_no_events();
        }
        return $content;
    }

    /**
     * Return an event as html card
     * 
     * @param object $item one YesTicket Event
     * 
     * @return string html for the event as card
     */
    private function render_single_card($item)
    {
        $time = ytp_to_local_datetime($item->event_datetime);
        $booking_url = $item->yesticket_booking_url;
        // picture size is 1200x628 px
        $picture_url = $item->event_picture_url;
        $ts = $time->getTimestamp();
        $month = wp_date('M', $ts);
        $day = wp_date('d', $ts);
        $year = wp_date('Y', $ts);
        $organizer_name = htmlentities($item->organizer_name);
        $event_name = htmlentities($item->event_name);
        $location_name = htmlentities($item->location_name);
        return <<<EOD
        <a href="$booking_url" target="_blank">
            <div class="ytp-event-card">
                <div class="ytp-event-card-image" style="background-image: url('$picture_url')"></div>
                <div class="ytp-event-card-text-wrapper">
                    <div class="ytp-event-card-date">
                        <span class="ytp-event-card-month">$month</span><br>
                        <span class="ytp-event-card-day">$day</span><br>
                        <span class="ytp-event-card-year">$year</span>
                    </div>
                    <div class="ytp-event-card-body">
                        <div class="ytp-event-card-body-fade-out"></div>
                        <small class="ytp-event-card-location">$location_name</small>
                        <strong class="ytp-event-card-title">$event_name</strong>
                    </div>
                </div>
            </div>
        </a>
EOD;
        // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
    }
}
