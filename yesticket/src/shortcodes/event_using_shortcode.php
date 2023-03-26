<?php

namespace YesTicket;

use \Exception;

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
  /**
   * @var Api
   */
  protected $api;

  /**
   * @param Api $api
   */
  protected function __construct($api)
  {
    parent::__construct(__DIR__ . '/templates');
    $this->api = $api;
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
    // @codeCoverageIgnoreStart
    return "<!-- STUB -->";
    // @codeCoverageIgnoreEnd
  }

  /**
   * @param array $atts — User defined attributes in shortcode tag.
   * @return array — Combined and filtered attribute list.
   */
  protected function shortCodeArgs($atts)
  {
    return \shortcode_atts(array(
      'env' => NULL,
      'api-version' => NULL,
      'organizer' => NULL,
      'key' => NULL,
      'type' => 'all',
      'count' => 100,
      'theme' => 'light',
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
      $result = $this->api->getEvents($att);
      if (!\is_countable($result) or \count($result) < 1) {
        $content .= $this->render_no_events();
      } else {
        $content .= $this->render_contents($result, $att);
      }
      //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
    } catch (Exception $e) {
      $content .= $e->getMessage();
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
   * Return html for "no events available"
   */
  protected function render_no_events()
  {
    /* translators: When no upcoming events can be found. */
    return '<p>' . __("At this time no upcoming events are available.", "yesticket") . '</p>';
  }

  /**
   * Print event type localized.
   * (Workaround to make the event $type translatable)
   * 
   * @param string $type of the event
   * 
   */
  public function render_eventType($type)
  {
    if (\strcasecmp('auftritt', $type) === 0) {
      /* translators: Event Type 'Performance' */
      return _e("Performance", "yesticket");
    }
    if ((\strcasecmp('workshop', $type) === 0) or (\strcasecmp('kurs', $type) === 0)) {
      /* translators: Event Type 'Workshop' */
      return _e("Workshop", "yesticket");
    }
    if (\strcasecmp('festival', $type) === 0) {
      /* translators: Event Type 'Festival' */
      return _e("Festival", "yesticket");
    }
    _e($type, 'yesticket');
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
