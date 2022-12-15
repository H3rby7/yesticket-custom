<?php

include_once("yesticket_options_helpers.php");
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

        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            foreach ($result as $item) {
                $add = "";
                $content .= "<div class='yt-row-list'>";
                if ($att["type"]=="all") {
                    $add = "<br><span class='yt-eventtype'>".$item->event_type."</span>";
                }
                $content .= "<span class='yt-eventdate'>".date('d.m.y H:i', strtotime($item->event_datetime))." Uhr</span>".$add."</span><br>";
                $content .= "<span class='yt-eventname'>".htmlentities($item->event_name)."</span>";

                $content .= "<span class='yt-eventdate'>".htmlentities($item->location_name).", ".htmlentities($item->location_city)."</span>";
                if ($att["ticketlink"]=="yes") {
                    $content .= '<br><a href="'.$item->yesticket_booking_url.'" target="_blank">Tickets</a>';
                }
                $content .= "</div>\n";
                $count++;
                if ($count == (int)$att["count"]) {
                    break;
                }
            }
        } else {
            $content = "<div><p>Im Moment keine aktuellen Veranstaltungen.</p>";
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function render_yesTicketEventsListHelp() {?>
    <h2>Shortcodes für deine Events als Liste</h2>
    <p>Schnellstart: <span class="yt-code">[yesticket_events_list type="all" count="3" theme="light"]</span>
    <h3>Optionen für Event-List-Shortcodes</h3>
    <?php 
    echo ytp_render_optionType('Events');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}

?>