<?php

namespace YesTicket;

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");
include_once(__DIR__ . "/../helpers/templater.php");

/**
 * Shortcode [yesticket_events]
 */
abstract class EventUsingShortcode extends Templater
{
    /**
     * Get the $instance
     * 
     * @return EventUsingShortcode $instance
     */
    abstract static public function getInstance();

    /**
     * The class to be applied in the root container of the shortcode
     * 
     * @var string
     */
    protected $cssClass = 'ytp-shortcode';

    protected function __construct()
    {
        parent::__construct(__DIR__ . '/templates');
    }

    /**
     * Static callback to add via add_shortcode
     * Use like array('ClassName', 'shortCode')
     * 
     * @param array $atts — User defined attributes in shortcode tag.
     * @return callback - To add with add_shortcode
     */
    static public function shortCode($atts)
    {
        // THIS IS A STUB to share the function documentation!
        // Overwrite in implementing class
        return "<!-- STUB -->";
    }

    /**
     * @param array $atts — User defined attributes in shortcode tag.
     * @return array — Combined and filtered attribute list.
     */
    protected function shortCodeArgs($atts)
    {
        // List all 'att' here, eventhough some 'att' are only used by some shortcodes
        return \shortcode_atts(array(
            'env' => NULL,
            'api-version' => NULL,
            'organizer' => NULL,
            'key' => NULL,
            'type' => 'all',
            'count' => 9,
            'theme' => 'light',
            'details' => 'no',
            'ticketlink' => 'no',
            'grep' => NULL,
        ), $atts);
    }

    /**
     * Return the rendered shortcode content as html elements
     * 
     * @param array $att shortcode attributes
     * 
     * @return string shortcode content
     */
    public function get($atts)
    {
        \wp_enqueue_style('yesticket');
        $att = $this->shortCodeArgs($atts);
        $content = \ytp_render_shortcode_container_div($this->cssClass, $att);
        try {
            $result = Api::getInstance()->getEvents($att);
            if (!\is_countable($result) or \count($result) < 1) {
                $content .= \ytp_render_no_events();
            } else if (\array_key_exists('message', $result) && $result->message == "no items found") {
                $content .= \ytp_render_no_events();
            } else {
                $content .= $this->render_contents($result, $att);
            }
            //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        } catch (\Exception $e) {
            $content .= __($e->getMessage(), 'yesticket');
        }
        $content .= "</div>\n";
        return $content;
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
     * Return html for the content as string.
     * 
     * @param array $result of the YesTicket API call for events
     * @param array $att shortcode attributes
     * 
     * @return string html as string for the content
     */
    abstract function render_contents($result, $att);
}
