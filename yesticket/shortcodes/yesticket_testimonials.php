<?php

include_once(__DIR__ . "/../yesticket_helpers.php");

add_shortcode('yesticket_testimonials', 'ytp_shortcode_testimonials');

function ytp_shortcode_testimonials($atts)
{
    $att = shortcode_atts(array(
        'env' => 'prod',
        'api-version' => '',
        'organizer' => '',
        'key' => '',
        'type' => 'all',
        'count' => '3',
        'theme' => 'light',
        'details' => 'no',
    ), $atts);
    return YesTicketTestimonials::getInstance()->get($att);
}

class YesTicketTestimonials
{
    static private $instance;
    static public function getInstance()
    {
        if (!isset(YesTicketTestimonials::$instance)) {
            YesTicketTestimonials::$instance = new YesTicketTestimonials();
        }
        return YesTicketTestimonials::$instance;
    }

    public function get($att)
    {
        $content = ytp_render_shortcode_container_div("ytp-testimonials", $att);
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

    private function render_single_testimonial($item, $att)
    {
        $text = htmlentities($item->text);
        $source = $this->render_source($item, $att["details"] == "yes");
        $about_event = "";
        return <<<EOD
        <div class='ytp-testimonial-row'><div>
            <span class="ytp-testimonial-text">&raquo;$text&laquo;</span>
            <span class="ytp-testimonial-source">$source</span>
        </div></div>
EOD;
        // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
    }

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
