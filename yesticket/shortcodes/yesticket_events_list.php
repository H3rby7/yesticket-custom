<?php

include_once("yesticket_options_helpers.php");
include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ . "/../yesticket_helpers.php");
include_once(__DIR__ . "/../yesticket_api.php");

add_shortcode('yesticket_events_list', 'ytp_shortcode_events_list');

function ytp_shortcode_events_list($atts)
{
    $att = shortcode_atts(array(
        'env' => 'prod',
        'api-version' => '',
        'organizer' => '',
        'key' => '',
        'type' => 'all',
        'count' => '100',
        'theme' => 'light',
        'ticketlink' => 'no',
    ), $atts);
    return YesTicketEventsList::getInstance()->get($att);
}

class YesTicketEventsList
{
    static private $instance;
    static public function getInstance()
    {
        if (!isset(YesTicketEventsList::$instance)) {
            YesTicketEventsList::$instance = new YesTicketEventsList();
        }
        return YesTicketEventsList::$instance;
    }

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


    private function render_list($result, $att)
    {
        $content = "<ol>\n";
        $count = 0;
        foreach ($result as $item) {
            $content .= $this->render_list_item($item, $att);
            $count++;
            if ($count == (int)$att["count"]) {
                break;
            }
        }
        $content .= "</ol>\n";
        return $content;
    }

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
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
    }

    public function render_help()
    { ?>
        <h2><?php echo __("Shortcodes for your events as list.", "yesticket"); ?></h2>
        <p><?php echo __("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_events_list type="all" count="3"]</span></p>
        <h3><?php echo __("Options for event list shortcodes", "yesticket"); ?></h3>
<?php
        echo ytp_render_optionType('events');
        echo ytp_render_optionCount();
    }
}
