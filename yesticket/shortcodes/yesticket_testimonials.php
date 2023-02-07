<?php

include_once("yesticket_options_helpers.php");
include_once(__DIR__ ."/../yesticket_helpers.php");
include_once(__DIR__ ."/../yesticket_api.php");

add_shortcode('yesticket_testimonials', 'ytp_getTestimonials');

function ytp_getTestimonials($atts)
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
        $result = ytp_api_getTestimonials($att);
        if (!is_countable($result) or count($result) < 1) {
            $content = ytp_render_no_events();
        } else if (array_key_exists('message', $result) && $result->message == "no items found") {
            $content = ytp_render_no_events();
        } else {
            $content .= ytp_render_testimonials($result, $att);
        }
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function ytp_render_testimonials($result, $att) {
    $content = "";
    $count = 0;
    foreach ($result as $item) {
        $add = "";
        $content .= "<div class='ytp-testimonial-row'>";
        if (!empty($item->event_name) && $att["details"] == "yes") {
            $add_event = '<br><span class="ytp-testimonial-source">'.__("about", "yesticket").' "'.htmlentities($item->event_name).'"</span>';
        }
        $content .= '<span class="ytp-testimonial-text">&raquo;'.htmlentities($item->text).'&laquo;</span><br>';
        $content .= '<span class="ytp-testimonial-source">'.ytp_render_testimonialSource($item).'</span>';
        $content .= '</div>';
        $count++;
        if ($count == (int)$att["count"]) {
            break;
        }
    }
    return $content;
}

function ytp_render_testimonialSource($item) {
    $source = $item->source;
    $date = $item->date;
    return sprintf(
        /* translators: %1$s is replaced with the author; %2$s is replaced with the date */
        __('%1$s on %2$s.', "yesticket" ),
        $source,
        ytp_render_date($date)
    );
}

function ytp_render_testimonialsHelp() {?>
    <h2><?php echo __("Shortcodes for your testimonials.", "yesticket");?></h2>
    <p><?php echo __("quickstart", "yesticket");?>: <span class="ytp-code">[yesticket_testimonials count="30"]</span></p>
    <h3><?php echo __("Options for testimonial shortcodes", "yesticket");?></h3>
    <h4>Details</h4>
    <p><?php echo __("Using details you can display the corresponding event to a testimonial.", "yesticket");?></p>
    <p class="ml-3"><span class="ytp-code">details="yes"</span> <?php 
    /* translators: The sentence actually starts with a non-translatable codeblock 'details="yes"'*/
    echo __("will add the event name to each testimonial, if present.", "yesticket");?></p>
    <?php 
    echo ytp_render_optionType('testimonials');
    echo ytp_render_optionCount();
    echo ytp_render_optionTheme();
}
?>