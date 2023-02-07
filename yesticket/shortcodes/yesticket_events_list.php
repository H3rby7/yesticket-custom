<?php

include_once("yesticket_options_helpers.php");
include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_events_list', 'ytp_getEventList');

function ytp_getEventList($atts)
{
    $att = shortcode_atts(array(
                    'organizer' => '',
                    'key' => '',
                    'ticketlink' => 'no',
                    'type' => 'all',
                    'env' => 'prod',
                    'count' => '100',
                    'theme' => 'light',
                    ), $atts);
    try {
        $result = ytp_api_getEvents($att);
        $content = "";
        if (!is_countable($result) or count($result) < 1) {
            $content = ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content = ytp_render_no_events();
        } else {
            $content .= ytp_render_eventList($result, $att);
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function ytp_render_eventList($result, $att) {
    $content = "<ol class='ytp-event-list'>";
    $count = 0;
    foreach ($result as $item) {
        $content .= ytp_render_eventListEntry($item, $att);
        $count++;
        if ($count == (int)$att["count"]) {
            break;
        }
    }
    $content .= "</ol>";
    return $content;
}

function ytp_render_eventListEntry($item, $att) {
    $event_date = ytp_render_date($item->event_datetime);
    $event_time = ytp_render_time($item->event_datetime);
    $event_name = htmlentities($item->event_name);
    $location_name = htmlentities($item->location_name);
    $location_city = htmlentities($item->location_city);
    $event_type = "";
    if ($att["type"]=="all") {
        $event_type = "<li class='ytp-event-list-type'>".ytp_render_eventType($item->event_type)."</li>";
    }
    $booking = "";
    if ($att["ticketlink"]=="yes") {
        $booking .= '<li class="ytp-event-list-tickets"><a href="'.$item->yesticket_booking_url.'" target="_blank">Tickets</a></li>';
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

function ytp_render_eventListHelp() {?>
    <h2><?php echo __("Shortcodes for your events as list.", "yesticket");?></h2>
    <p><?php echo __("quickstart", "yesticket");?>: <span class="ytp-code">[yesticket_events_list type="all" count="3"]</span></p>
    <h3><?php echo __("Options for event list shortcodes", "yesticket");?></h3>
    <?php 
    echo ytp_render_optionType('events');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}

?>