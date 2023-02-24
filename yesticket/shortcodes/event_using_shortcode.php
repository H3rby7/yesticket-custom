<?php

include_once(__DIR__ . "/../helpers/api.php");
include_once(__DIR__ . "/../helpers/functions.php");

/**
 * Shortcode [yesticket_events]
 */
abstract class YesTicketEventUsingShortcode
{
    /**
     * Get the $instance
     * 
     * @return YesTicketEventUsingShortcode $instance
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
        $content = ytp_render_shortcode_container_div($this->cssClass, $att);
        try {
            $result = YesTicketApi::getInstance()->getEvents($att);
            if (!is_countable($result) or count($result) < 1) {
                $content .= ytp_render_no_events();
            } else if (array_key_exists('message', $result) && $result->message == "no items found") {
                $content .= ytp_render_no_events();
            } else {
                $content .= $this->render_contents($result, $att);
            }
            //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        } catch (Exception $e) {
            $content .= __($e->getMessage(), 'yesticket');
        }
        $content .= "</div>\n";
        return $content;
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
