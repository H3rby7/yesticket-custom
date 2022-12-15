<?php

include_once("yesticket_options_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_testimonials', 'getYesTicketTestimonials');

function getYesTicketTestimonials($atts)
{
    $att = shortcode_atts(array(
                    'organizer' => '',
                    'key' => '',
                    'count' => '3',
                    'type' => 'all',
                    'details' => 'no',
                    'env' => 'prod',
                    'theme' => 'light',
                    ), $atts);
    $content = "";
    $env_add = "";
    if ($att["env"] == 'dev') {
        $env_add = "/dev";
    }
    try {
        $options = get_option('yesticket_settings');
        validateArguments($att, $options);
        // Get it from API URL:
        $get_url = "https://www.yesticket.org".$env_add."/api/v2/testimonials.php";
        $get_url .= buildYesticketQueryParams($atts, $options);
        $result = getDataCached($get_url);
        //////////

        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            foreach ($result as $item) {
                $add = "";
                $content .= "<div class='yt-testimonial-row'>";
                if (!empty($item->event_name) && $att["details"] == "yes") {
                    $add_event = "<br><span class='yt-testimonial-source'>über ".htmlentities($item->event_name)."</span>";
                }
                $content .= "<span class='yt-testimonial-text'>&raquo;".htmlentities($item->text).'&laquo;</span><br>'."<span class='yt-testimonial-source'>".htmlentities($item->source).' '."</span> <span class='yt-testimonial-date'>Am ".htmlentities(date('d.m.Y', strtotime($item->date)))."</span>".$add_event;
                $content .= "</div>\n";
                $count++;
                if ($count == (int)$att["count"]) {
                    break;
                }
            }
        } else {
            $content = "";
        }
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function render_yesTicketTestimonialsHelp() {?>
    <h2>Shortcodes für Zuschauerstimmen</h2>
    <p>Schnellstart: <span class="yt-code">[yesticket_testimonials count="30"]</span></p>
    <h3>Optionen für Testimonial-Shortcodes</h3>
    <?php 
    echo ytp_render_optionType('Testimonials');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}
?>