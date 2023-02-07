<?php

include_once("yesticket_options_helpers.php");
include_once("yesticket_shortcode_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_events', 'ytp_getEvents');

function ytp_getEvents($atts)
{
    $att = shortcode_atts(array(
                    'organizer' => '',
                    'key' => '',
                    'details' => 'no',
                    'type' => 'all',
                    'env' => 'prod',
                    'count' => '100',
                    'theme' => 'light',
                    ), $atts);
    $content = "";
    try {
        $result = ytp_api_getEvents($att);
        if ($att["theme"] == "light") {
            $content .= "<div class='ytp-light'>";
        } elseif ($att["theme"] == "dark") {
            $content .= "<div class='ytp-dark'>";
        } else {
            $content .= "<div class='ytp-default ".$att["theme"]."'>";
        }
        if (!is_countable($result) or count($result) < 1) {
            $content = ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content = ytp_render_no_events();
        } else {
            $content .= ytp_render_events($result, $att);
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function ytp_render_events($result, $att) {
    $content = "";
    $count = 0;
    foreach ($result as $item) {
        $add = "";
        $content .= "<div class='ytp-row'>";
        if ($att["type"]=="all") {
            $add = " <span class='ytp-eventtype'>".ytp_render_eventType($item->event_type)."</span>";
        }
        $content .= '<a href="'.$item->yesticket_booking_url.'" target="_blank" class="ytp-button">'.__("Tickets", "yesticket").' <img src="'. ytp_getImageUrl('YesTicket_260x260.png') .'" height="20" width="20"></a>';
        $content .= "<span class='ytp-eventdate'>".ytp_render_date_and_time($item->event_datetime)."</span>".$add;
        $content .= "<span class='ytp-eventname'>".htmlentities($item->event_name)."</span>";

        $content .= "<span class='ytp-eventdate'>".htmlentities($item->location_name).", ".htmlentities($item->location_city)."</span>";
        if (!empty($item->event_urgency_string)) {
            $content.= "<br><span class='ytp-urgency'>".htmlentities($item->event_urgency_string).""."</span>";
        }
        if ($att["details"] == "yes") {
            $details = nl2br(htmlentities($item->event_description));
            if (!empty($item->event_notes_help)) {
                $details .= "<h5>".__("Hints", "yesticket")."</h5>".nl2br(htmlentities($item->event_notes_help));
            }
            $details .= "<h5>".__("Tickets", "yesticket")."</h5>".htmlentities($item->tickets);
            $details .= "<h5>".__("Location", "yesticket")."</h5>".htmlentities($item->location_name)."<br>".htmlentities($item->location_street)."<br>".htmlentities($item->location_zip)." ".htmlentities($item->location_city).", ".htmlentities($item->location_state).", ".htmlentities($item->location_country);
            $content .= '<br><details class="ytp-details">
                            <summary><u class="ytp-show-details">'.__("Show details", "yesticket").'</u></summary>
                            <div>'.$details.'</div><div class="ytp-button-row"><a href="'.$item->yesticket_booking_url.'" target="_blank" class="ytp-button-big">'.__("Order Tickets", "yesticket").'<img src="'.ytp_getImageUrl('YesTicket_260x260.png').'" height="20" width="20">'.'</a></div>'."
                        </details>";
        }
        $content .= "</div>\n";
        $count++;
        if ($count == (int)$att["count"]) {
            break;
        }
    }
    return $content;
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