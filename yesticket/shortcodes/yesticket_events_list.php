<?php

include_once("yesticket_options_helpers.php");
include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_events_list', 'getYesTicketEventsList');

function getYesTicketEventsList($atts)
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
        $result = getEventsFromApi($att);
        $content = "";
        if (!is_countable($result) or count($result) < 1) {
            $content = ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content = ytp_render_no_events();
        } else {
            $content .= render_yesTicketEventsList($result, $att);
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function render_yesTicketEventsList($result, $att) {
    $content = "";
    $count = 0;
    foreach ($result as $item) {
        $add = "";
        $content .= "<div class='ytp-row-list'>";
        if ($att["type"]=="all") {
            $add = "<br><span class='ytp-eventtype'>".ytp_render_eventType($item->event_type)."</span>";
        }
        $content .= "<span class='ytp-eventdate'>".ytp_render_date_and_time($item->event_datetime)."</span>".$add."</span><br>";
        $content .= "<span class='ytp-eventname'>".htmlentities($item->event_name)."</span>";
        $content .= "<span class='ytp-eventdate'>".htmlentities($item->location_name).", ".htmlentities($item->location_city)."</span>";
        if ($att["ticketlink"]=="yes") {
            $content .= '<br><a href="'.$item->yesticket_booking_url.'" target="_blank">Tickets</a>';
        }
        $content .= "</div>\n";
        $count++;
        if ($count == (int)$att["count"]) {
            break;
        }
    }
    return $content;
}

function render_yesTicketEventsListHelp() {?>
    <h2><?php echo __("Shortcodes for your events as list.", "yesticket");?></h2>
    <p><?php echo __("quickstart", "yesticket");?>: <span class="ytp-code">[yesticket_events_list type="all" count="3"]</span></p>
    <h3><?php echo __("Options for event list shortcodes", "yesticket");?></h3>
    <?php 
    echo ytp_render_optionType('events');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}

?>