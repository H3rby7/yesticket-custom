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
    try {
        $result = getTestimonialsFromApi($att);
        if (!is_countable($result) or count($result) < 1) {
            $content = ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content = ytp_render_no_events();
        } else {
            $content .= render_yesTicketTestimonials($result, $att);
        }
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function render_yesTicketTestimonials($result, $att) {
    $content = "";
    $count = 0;
    foreach ($result as $item) {
        $add = "";
        $content .= "<div class='yt-testimonial-row'>";
        if (!empty($item->event_name) && $att["details"] == "yes") {
            $add_event = '<br><span class="yt-testimonial-source">'.__("about", "yesticket").' "'.htmlentities($item->event_name).'"</span>';
        }
        $content .= '<span class="yt-testimonial-text">&raquo;'.htmlentities($item->text).'&laquo;</span><br>'.'<span class="yt-testimonial-source">'.htmlentities($item->source).' </span> <span class="yt-testimonial-date">'.__("date on", "yesticket").' '.htmlentities(date('d.m.Y', strtotime($item->date))).'</span>';
        $content .= '</div>';
        $count++;
        if ($count == (int)$att["count"]) {
            break;
        }
    }
    return $content;
}

function render_yesTicketTestimonialsHelp() {?>
    <h2><?php echo __("Shortcodes for your testimonials.", "yesticket");?></h2>
    <p><?php echo __("quickstart", "yesticket");?>: <span class="yt-code">[yesticket_testimonials count="30"]</span></p>
    <h3><?php echo __("Options for testimonial shortcodes", "yesticket");?></h3>
    <h4>Details</h4>
    <p class='ml-3'><?php echo __("Using details you can display the corresponding event to a testimonial.", "yesticket");?></p>
    <p class="ml-3"><span class="yt-code">details="yes"</span> <?php echo __("will add the event name to each testimonial, if present.", "yesticket");?></p>
    <?php 
    echo ytp_render_optionType('testimonials');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}
?>