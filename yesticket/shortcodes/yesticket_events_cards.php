<?php

include_once("yesticket_options_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_events_cards', 'getYesTicketEventsCards');

function getYesTicketEventsCards($atts) {
	$att = shortcode_atts( array(
			'organizer' => '',
			'key' => '',
            'details' => 'no',
			'type' => 'all',
			'env' => 'prod',
			'count' => '100',
			'grep' => '',
			'theme' => 'light',
			), $atts );
    try {
        $result = getEventsFromApi($att);
        $content = "";
        if ($att["theme"] == "light") {
                $content .= "<div class='yt-light'>";
        }
        else if ($att["theme"] == "dark") {
                $content .= "<div class='yt-dark'>";
        }
        else {
            $content .= "<div class='yt-default ".$att["theme"]."'>";
        }

        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            if ((int)$att["count"] === 1) {
                $content .= "<div class='yt-single'>\n";
            } else {
                $content .= "<div class='yt-container'>\n";
            }
            foreach($result as $item){
                if (!empty($att["grep"])) {
                    if (!str_contains($item->event_name, $att["grep"])) {
                        // Did not find the required Substring in the event_title, skip this event
                        continue;
                    }
                }
                $time = strtotime($item->event_datetime);
                $content .= '<div class="yt-card-event">'."\n".'<a href="'.$item->yesticket_booking_url.'" target="_new">'."\n".'<div class="yt-card">';
                    // START 'Wrapper' [div > a > div(yt-card)]
                    // START 'img'
                    $content .= '<div class="yt-card-image-wrapper">'."\n";
                        $content .= '<img src="'.$item->event_picture_url.'" alt="Eventbild">'."\n";
                    $content .= '</div>'."\n";
                    // END 'img'
                    // START 'text'
                    $content .= '<div class="yt-card-text-wrapper">'."\n";
                        // START 'DATE'
                        $content .= '<div class="yt-card-date">'."\n";
                            $content .= '<span class="yt-card-month">'.date('M', $time).'</span><br>'."\n";
                            $content .= '<strong class="yt-card-day">'.date('d', $time).'</strong><br>'."\n";
                            $content .= '<span class="yt-card-year">'.date('Y', $time).'</span>'."\n";
                        $content .= '</div>'."\n";
                        // END 'DATE'
                        // START 'Body // The Event'
                        $content .= '<div class="yt-card-body">'."\n";
                            $content .= '<span class="yt-card-body-organizer">'.htmlentities($item->organizer_name).'</span><br>'."\n";
                            $content .= '<strong class="yt-card-body-title">'.htmlentities($item->event_name).'</strong><br>'."\n";
                            $content .= '<small class="yt-card-body-location">'.htmlentities($item->location_name).'</small>'."\n";
                        $content .= '</div>'."\n";
                        // END 'Body // The Event'
                    $content .= '</div>'."\n";
                    // END 'text'
                $content .= "</div>\n</a>\n</div>";
                // END 'Wrapper' [div > a > div(yt-card)]
                $count++;
                if ($count == (int)$att["count"]) break;
            }
            $content .= "</div>\n";
        } else {
            $content = "<p>Im Moment keine aktuellen Veranstaltungen.</p>";
        }
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function render_yesTicketEventsCardsHelp() {
    ytp_h(2, 'Shortcodes für deine Events als Kacheln bzw. Cards');
    ?><p><?php ytp_translate('Schnellstart');?>: <span class="yt-code">[yesticket_events_cards count="30"]</span></p><?php 
    ytp_h(3, 'Optionen für Event-Card-Shortcodes');
    echo ytp_render_optionType('Events');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
    ?>
    <h4>Grep</h4>
    <p class='ml-3'><?php ytp_translate('Mit <b>grep</b> kannst du die Liste der Events über den Titel filtern.');?></p>
    <p class="ml-3"><span class="yt-code">grep="Johnstone"</span> <?php ytp_translate('werden nur Events angezeigt, die im Event Titel irgendwo die Zeichenfolge "Johnstone" enthalten.');?></p>
<?php } ?>
