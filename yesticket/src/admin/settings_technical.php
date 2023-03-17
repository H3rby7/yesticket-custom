<?php

namespace YesTicket\Admin;

use YesTicket\Cache;
use YesTicket\ImageCache;
use YesTicket\PluginOptions;

include_once("settings_section.php");
include_once(__DIR__ . "/../helpers/cache.php");
include_once(__DIR__ . "/../helpers/plugin_options.php");

/**
 * YesTicket's technical settings
 */
class SettingsTechnical extends SettingsSection
{
  /**
   * DB connection used to clear cache
   * @var wpdb
   * @see https://developer.wordpress.org/reference/classes/wpdb/
   */
  private $wpdb = null;

  /**
   * Constructor.
   *
   * @param string $parent_slug for the menu hirarchy
   * @param string $wpdb DB connection
   */
  public function __construct($parent_slug, $wpdb)
  {
    parent::__construct($parent_slug);
    $this->wpdb = $wpdb;
    $this->configure();
  }

  /**
   * Configure the technical settings using the Settings API.
   */
  public function configure()
  {

    PluginOptions::getInstance()->register_settings_technical($this->get_slug());

    // Register technical section and fields
    \add_settings_section(
      $this->get_slug(),
      __("Technical Settings", "yesticket"),
      array($this, 'render_technical_section'),
      $this->get_slug()
    );
    \add_settings_field(
      $this->get_slug() . '-cache_time',
      __("Cache time in minutes", "yesticket"),
      array($this, 'render_cache_time'),
      $this->get_slug(),
      $this->get_slug()
    );
  }

  /**
   * Get the slug used by the technical settings page.
   *
   * @return string
   */
  public function get_slug()
  {
    return $this->get_parent_slug() . '-technical';
  }

  /**
   * Prints the technical settings.
   */
  public function render()
  {
    $action = \esc_url(\admin_url('options.php'));
    $tab = isset($_GET['tab']) ? ('&tab=' . $_GET['tab']) : '';
    $this->render_template('settings_technical', \compact("action", "tab"));
  }

  /**
   * Prints the cache_time field.
   */
  public function render_cache_time()
  {
    $cache_time = PluginOptions::getInstance()->getCacheTimeInMinutes();
    print <<<EOD
        <input type='number' 
               name='yesticket_settings_technical[cache_time_in_minutes]' 
               min="0" 
               step="1" 
               value='$cache_time'>
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
  }

  /**
   * Prints the technical settings section header.
   */
  public function render_technical_section()
  {
    _e("Change these settings at your own risk.", "yesticket");
  }

  /**
   * Clears the YesTicket cached API requests.
   * 
   * @return string the corresponding success message as html elements
   */
  private function clear_cache()
  {
    Cache::clear($this->wpdb);
    return $this->success_message(
      /* translators: Success Message after clearing cache */
      __("Deleted the cache.", "yesticket")
    );
  }

  /**
   * Check to clear cache and return success feedback
   * 
   * @return string feedback as html elements
   */
  public function feedback()
  {
    if (isset($_POST['clear-cache'])) {
      return $this->clear_cache();
    }
  }
}
