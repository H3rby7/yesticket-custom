<?php

namespace YesTicket;

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");
include_once(__DIR__ . "/../helpers/templater.php");

\add_shortcode('yesticket_testimonials', 'YesTicket\shortcode_testimonials');

/**
 * Callback to add_shortcode [yesticket_testimonials]
 */
function shortcode_testimonials($atts)
{
    \wp_enqueue_style('yesticket');
    $att = \shortcode_atts(array(
        'env' => NULL,
        'api-version' => NULL,
        'organizer' => NULL,
        'key' => NULL,
        'type' => 'all',
        'count' => '9',
        'theme' => 'light',
        'design' => 'basic',
        'details' => 'no',
    ), $atts);
    return Testimonials::getInstance()->get($att);
}

/**
 * Shortcode [yesticket_testimonials]
 */
class Testimonials extends Templater
{
    /**
     * The $instance
     *
     * @var Testimonials
     */
    static private $instance;

    /**
     * Get the $instance
     * 
     * @return Testimonials $instance
     */
    static public function getInstance()
    {
        if (!isset(Testimonials::$instance)) {
            Testimonials::$instance = new Testimonials();
        }
        return Testimonials::$instance;
    }

    protected function __construct()
    {
        parent::__construct(__DIR__ . '/templates');
    }

    /**
     * Return the given template as string, if it's readable.
     *
     * @param string $template
     * @param array $variables passed via 'compact', to be used via 'extract'
     */
    protected function render_template($template, $variables = array())
    {
        \ob_start();
        parent::render_template($template, $variables);
        return \ob_get_clean();
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
        $content = \ytp_render_shortcode_container_div($classes, $att);
        try {
            $result = Api::getInstance()->getTestimonials($att);
            if (!\is_countable($result) or \count($result) < 1) {
                $content .= \ytp_render_no_events();
            } else if (\array_key_exists('message', $result) && $result->message == "no items found") {
                $content .= \ytp_render_no_events();
            } else {
                $content .= $this->render_testimonials($result, $att);
            }
        } catch (\Exception $e) {
            $content .= \__($e->getMessage(), 'yesticket');
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
        if (\strcasecmp($design, "basic") == 0 || \strcasecmp($design, "jump") == 0) {
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
        foreach ($result as $item) {
            $content .= $this->render_template('testimonial_row', \compact("item", "att"));
        }
        return $content;
    }

    /**
     * Print the source of a testimonial as html
     * 
     * @param object $item of the YesTicket API call for testimonials
     * @param array $includeEventName whether or not to include the corresponding event name
     * 
     */
    function render_source($item, $includeEventName)
    {
        $source = $item->source;
        $date = $item->date;
        $event = $item->event_name;
        if (!$includeEventName || $event === null || \trim($event) === '') {
            \printf(
                /* translators: Used when producing the testimonial source - %1$s is replaced with the author; %2$s is replaced with the date; %3$s is replaced with the event_name */
                \__('%1$s on %2$s.', "yesticket"),
                $source,
                \ytp_render_date($date)
            );
        }
        \printf(
            /* translators: Used when producing the testimonial source - %1$s is replaced with the author; %2$s is replaced with the date; %3$s is replaced with the event_name */
            \__('%1$s on %2$s about \'%3$s\'.', "yesticket"),
            $source,
            \ytp_render_date($date),
            \htmlentities($event)
        );
    }
}
