<?php

include_once("yesticket_options_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_events', 'getYesTicketEvents');

function getYesTicketEvents($atts)
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
        $result = getEventsFromApi($att);
        if ($att["theme"] == "light") {
            $content .= "<div class='yt-light'>";
        } elseif ($att["theme"] == "dark") {
            $content .= "<div class='yt-dark'>";
        } else {
            $content .= "<div class='yt-default ".$att["theme"]."'>";
        }
        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            foreach ($result as $item) {
                $add = "";
                $content .= "<div class='yt-row'>";
                if ($att["type"]=="all") {
                    $add = " <span class='yt-eventtype'>".$item->event_type."</span>";
                }
                $content .= '<a href="'.$item->yesticket_booking_url.'" target="_blank" class="yt-button">Tickets <img src="'. ytp_getImageUrl('YesTicket_260x260.png') .'" height="20" width="20"></a>';
                $content .= "<span class='yt-eventdate'>".date('d.m.y H:i', strtotime($item->event_datetime))." Uhr</span>".$add;
                $content .= "<span class='yt-eventname'>".htmlentities($item->event_name)."</span>";
    
                $content .= "<span class='yt-eventdate'>".htmlentities($item->location_name).", ".htmlentities($item->location_city)."</span>";
                if (!empty($item->event_urgency_string)) {
                    $content.= "<br><span class='yt-urgency'>".htmlentities($item->event_urgency_string).""."</span>";
                }
                if ($att["details"] == "yes") {
                    $details = nl2br(htmlentities($item->event_description))."<br><br>";
                    if (!empty($item->event_notes_help)) {
                        $details .= "Hinweise: ".nl2br(htmlentities($item->event_notes_help))."<br><br>";
                    }
                    $details .= "Tickets:<br>".htmlentities($item->tickets)."<br><br>";
                    $details .= "Spielort:<br>".htmlentities($item->location_name)."<br>".htmlentities($item->location_street)."<br>".htmlentities($item->location_zip)." ".htmlentities($item->location_city).", ".htmlentities($item->location_state).", ".htmlentities($item->location_country);
                    $content .= '<br><details>
                                  <summary><u class="yt-show-details">Details anzeigen</u></summary>
                                  <p>'.$details.'</p><div class="yt-button-row"><a href="'.$item->yesticket_booking_url.'" target="_blank" class="yt-button-big">Tickets ordern<img src="'.ytp_getImageUrl('YesTicket_260x260.png').'" height="20" width="20">'.'</a></div>'."
                                </details>";
                }
                $content .= "</div>\n";
                $count++;
                if ($count == (int)$att["count"]) {
                    break;
                }
            }
        } else {
            $content = "<p>Im Moment keine aktuellen Veranstaltungen.</p>";
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function render_yesTicketEventsHelp() {?>
    <h2><?php echo __('Shortcodes für deine Events als interaktive Liste', 'yesticket');?></h2>
    <p><?php echo __('Schnellstart', 'yesticket');?>: <span class="yt-code">[yesticket_events type="all" count="3"]</span></p>
    <h3><?php echo __('Optionen für Event-Shortcodes', 'yesticket');?></h3>
    <h4><?php echo __('Details', 'yesticket');?></h4>
    <p class='ml-3'><?php echo __('Mit <b>details</b> kannst du die Beschreibung zu den Events anzeigen, die in YesTicket hinterlegt ist. Die sind per Link auf- und zuklappbar.', 'yesticket');?></p>
    <p class="ml-3"><span class="yt-code">details="yes"</span> <?php echo __('zeigt den Link zu Aufklappen und die Beschreibung an.', 'yesticket');?></p>
    <?php
    echo ytp_render_optionType('events');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}
?>