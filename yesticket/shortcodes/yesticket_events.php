<?php

include_once("yesticket_options_helpers.php");
include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_events', 'ytp_getEvents');

function ytp_getEvents($atts)
{
    $att = shortcode_atts(array(
        'env' => 'prod',
        'api-version' => '',
        'organizer' => '',
        'key' => '',
        'type' => 'all',
        'count' => '100',
        'theme' => 'light',
        'details' => 'no',
    ), $atts);
    $content = ytp_render_shortcode_container_div("ytp-events", $att);
    try {
        $result = YesTicketApi::getInstance()->getEvents($att);
        if (!is_countable($result) or count($result) < 1) {
            $content .= ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content .= ytp_render_no_events();
        } else {
            $content .= ytp_render_events($result, $att);
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    $content .= "</div>\n";
    return $content;
}

function ytp_render_events($result, $att) {
    $content = "";
    $count = 0;
    foreach ($result as $item) {
        $content .= ytp_render_event($item, $att);
        $count++;
        if ($count == (int)$att["count"]) {
            break;
        }
    }
    return $content;
}

function ytp_render_event($item, $att) {
    $booking_url = $item->yesticket_booking_url;
    $ticket_text = __("Tickets", "yesticket");
    $event_datetime = ytp_render_date_and_time($item->event_datetime);
    $event_name = htmlentities($item->event_name);
    $location_name = htmlentities($item->location_name);
    $location_city = htmlentities($item->location_city);
    $event_type = "";
    if ($att["type"]=="all") {
        $event_type = "<span class='ytp-event-type'>".ytp_render_eventType($item->event_type)."</span>";
    }
    $urgency = "";
    if (!empty($item->event_urgency_string)) {
        $urgency = "<span class='ytp-event-urgency'>".htmlentities($item->event_urgency_string)."</span>";
    }
    $details = "";
    if ($att["details"] == "yes") {
        $details = ytp_render_event_details($item);
    }
    return <<<EOD
    <div class='ytp-event-row'>
        <h3 class='ytp-event-name'>$event_name $event_type</h3>
        <div class="ytp-event-ticket-wrap">
            <div>$urgency</div>
            <div><a href="'.$booking_url.'" target="_blank" class="ytp-button">$ticket_text</a></div>
        </div>
        <span class='ytp-event-location'>$location_name</span>
        <span class='ytp-event-city'>$location_city</span>
        <span class='ytp-event-date'>$event_datetime</span>
        $details
    </div>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function ytp_render_event_details($item) {
    $event_description = nl2br(htmlentities($item->event_description));
    $show_details_text = __("Show details", "yesticket");
    $hints_heading = __("Hints", "yesticket");
    $hints_text = nl2br(htmlentities($item->event_notes_help));
    $tickets_heading = __("Tickets", "yesticket");
    $ticket_text = htmlentities($item->tickets);
    $location_heading = __("Location", "yesticket");
    $location_info = ytp_render_event_details_location_info($item);
    $booking_url = $item->yesticket_booking_url;
    $order_ticketes_text = __("Order Tickets", "yesticket");
    return <<<EOD
    <details class="ytp-event-details">
        <summary class="ytp-event-show-details">$show_details_text</summary>
        <div>
            <h5>$hints_heading</h5>
            $hints_text
            <h5>$tickets_heading</h5>
            $ticket_text
            <h5>$location_heading</h5>
            $location_info
        </div>
        <div class="ytp-event-button-row">
            <a href="$booking_url" target="_blank" class="ytp-button-big">$order_ticketes_text</a>
        </div>
    </details>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function ytp_render_event_details_location_info($item) {
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
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function ytp_render_eventsHelp() {?>
    <h2><?php echo __("Shortcodes for your events as interactive list.", "yesticket");?></h2>
    <p><?php echo __("quickstart", "yesticket");?>: <span class="ytp-code">[yesticket_events type="all" count="3"]</span></p>
    <h3><?php echo __("Options for event shortcodes", "yesticket");?></h3>
    <h4>Details</h4>
    <p><?php echo __("Using <b>details</b> you can include the description of your YesTicket event. The description is collapsed and can be expanded.", "yesticket");?></p>
    <p class="ml-3"><span class="ytp-code">details="yes"</span><?php
    /* translators: The sentence actually starts with a non-translatable codeblock 'details="yes"'*/
    echo __("will show a link to expand the details.", "yesticket");?></p>
    <?php
    echo ytp_render_optionType('events');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}
?>