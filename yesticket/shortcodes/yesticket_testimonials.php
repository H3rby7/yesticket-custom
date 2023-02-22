<?php

include_once(__DIR__ . "/../yesticket_helpers.php");

add_shortcode('yesticket_testimonials', 'ytp_shortcode_testimonials');

/**
 * Callback to add_shortcode [yesticket_testimonials]
 */
function ytp_shortcode_testimonials($atts)
{
    wp_enqueue_style('yesticket');
    $att = shortcode_atts(array(
        'env' => 'prod',
        'api-version' => '',
        'organizer' => '',
        'key' => '',
        'type' => 'all',
        'count' => '3',
        'theme' => 'light',
        'design' => 'basic',
        'details' => 'no',
    ), $atts);
    return YesTicketTestimonials::getInstance()->get($att);
}

/**
 * Shortcode [yesticket_testimonials]
 */
class YesTicketTestimonials
{
    /**
     * The $instance
     *
     * @var YesTicketTestimonials
     */
    static private $instance;

    /**
     * Get the $instance
     * 
     * @return YesTicketTestimonials $instance
     */
    static public function getInstance()
    {
        if (!isset(YesTicketTestimonials::$instance)) {
            YesTicketTestimonials::$instance = new YesTicketTestimonials();
        }
        return YesTicketTestimonials::$instance;
    }

    /**
     * Return the rendered shortcode content as html elements
     * 
     * @param array $att shortcode attributes
     * 
     * @return string shortcode content
     */
    public function get($att)
    {
        $classes = $this->getDesignClasses($att);
        $content = ytp_render_shortcode_container_div($classes, $att);
        try {
            $result = YesTicketApi::getInstance()->getTestimonials($att);
            if (!is_countable($result) or count($result) < 1) {
                $content .= ytp_render_no_events();
            } else if (array_key_exists('message', $result) && $result->message == "no items found") {
                $content .= ytp_render_no_events();
            } else {
                $content .= $this->render_testimonials($result, $att);
            }
        } catch (Exception $e) {
            $content .= __($e->getMessage(), 'yesticket');
        }
        $content .= "</div>\n";
        return $content;
    }

    /**
     * Get css classes for the shortcode container
     * @param array $att the shortcode parameters
     * @return string css classes to put in attribute 'class=...'
     */
    private function getDesignClasses($att)
    {
        $shortcode_class = "ytp-testimonials";
        if (!isset($att["design"])) {
            return $shortcode_class;
        }
        $design = $att["design"];
        if (strcasecmp($design, "basic") == 0 || strcasecmp($design, "jump") == 0) {
            return "$shortcode_class ytp-$design";
        }
        return $shortcode_class . " design-must-be-basic-or-jump";
    }

    /**
     * Return the testimonials as html
     * 
     * @param array $result of the YesTicket API call for testimonials
     * @param array $att shortcode attributes
     * 
     * @return string html for the testimonials
     */
    private function render_testimonials($result, $att)
    {
        $content = "";
        $count = 0;
        foreach ($result as $item) {
            $content .= $this->render_single_testimonial($item, $att);
            $count++;
            if ($count == (int)$att["count"]) {
                break;
            }
        }
        return $content;
    }

    /**
     * Return one testimonial as html
     * 
     * @param object $item of the YesTicket API call for testimonials
     * @param array $att shortcode attributes
     * 
     * @return string html for the testimonial
     */
    private function render_single_testimonial($item, $att)
    {
        $text = htmlentities($item->text);
        $source = $this->render_source($item, $att["details"] == "yes");
        return <<<EOD
        <div class='ytp-testimonial-row'><div>
            <span class="ytp-testimonial-text">&raquo;$text&laquo;</span>
            <span class="ytp-testimonial-source">$source</span>
        </div></div>
EOD;
        // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
    }

    /**
     * Return the source of a testimonial as html
     * 
     * @param object $item of the YesTicket API call for testimonials
     * @param array $includeEventName whether or not to include the corresponding event name
     * 
     * @return string html for the source
     */
    private function render_source($item, $includeEventName)
    {
        $source = $item->source;
        $date = $item->date;
        $event = $item->event_name;
        if (!$includeEventName || $event === null || trim($event) === '') {
            return sprintf(
                /* translators: Used when producing the testimonial source - %1$s is replaced with the author; %2$s is replaced with the date; %3$s is replaced with the event_name */
                __('%1$s on %2$s.', "yesticket"),
                $source,
                ytp_render_date($date)
            );
        }
        return sprintf(
            /* translators: Used when producing the testimonial source - %1$s is replaced with the author; %2$s is replaced with the date; %3$s is replaced with the event_name */
            __('%1$s on %2$s about \'%3$s\'.', "yesticket"),
            $source,
            ytp_render_date($date),
            htmlentities($event)
        );
    }
}
