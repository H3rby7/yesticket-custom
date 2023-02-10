<?php

include_once("yesticket_options_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_events_cards', 'ytp_getEventCards');

function ytp_getEventCards($atts) {
	$att = shortcode_atts( array(
			'organizer' => '',
			'key' => '',
            'details' => 'no',
			'type' => 'all',
			'env' => 'prod',
			'count' => '6',
			'grep' => '',
			'theme' => 'light',
			), $atts );
    $content = ytp_render_shortcode_container_div("ytp-event-cards", $att);
    try {
        $result = ytp_api_getEvents($att);
        if (!is_countable($result) or count($result) < 1) {
            $content .= ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content .= ytp_render_no_events();
        } else {
            $content .= ytp_render_eventCards($result, $att);
        }
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    $content .= "</div>\n";
    return $content;
}

function ytp_render_eventCards($result, $att) {
    $content = "";
    $count = 0;
    foreach($result as $item){
        if (!empty($att["grep"])) {
            if (!str_contains($item->event_name, $att["grep"])) {
                // Did not find the required Substring in the event_title, skip this event
                continue;
            }
        }
        $content .= ytp_render_eventCard($item);
        $count++;
        if ($count == (int)$att["count"]) break;
    }
    return $content;
}

function ytp_render_eventCard($item) {
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
    <a href="$booking_url" target="_new">
        <div class="ytp-event-card">
            <div class="ytp-event-card-image" style="background-image: url('$picture_url')"></div>
            <div class="ytp-event-card-text-wrapper">
                <div class="ytp-event-card-date">
                    <span class="ytp-event-card-month">$month</span><br>
                    <span class="ytp-event-card-day">$day</span><br>
                    <span class="ytp-event-card-year">$year</span>
                </div>
                <div class="ytp-event-card-body">
                    <small class="ytp-event-card-location">$location_name</small><br>
                    <strong class="ytp-event-card-title">$event_name</strong><br>
                    <!--<span class="ytp-event-card-organizer">$organizer_name</span>-->
                </div>
            </div>
        </div>
    </a>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}

function ytp_render_eventCardsHelp() {?>
    <h2><?php echo __("Shortcodes for your events as cards.", "yesticket");?></h2>
    <p><?php echo __("quickstart", "yesticket");?>: <span class="ytp-code">[yesticket_events_cards count="30"]</span></p>
    <h3><?php echo __("Options for event card shortcodes", "yesticket");?></h3>
    <?php 
    echo ytp_render_optionType('events');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
    ?>
    <h4>Grep</h4>
    <p><?php
    echo __("Using <b>grep</b> you can filter your events by their title.", "yesticket");?></p>
    <p class="ml-3"><span class="ytp-code">grep="Johnstone"</span> <?php
    /* translators: The sentence actually starts with a non-translatable codeblock 'grep="Johnstone"'*/
    echo __("will only display events, who have \"Johnstone\" in their title.", "yesticket");?></p>
<?php } ?>
