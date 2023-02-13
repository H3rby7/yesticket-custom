<?php

include_once(__DIR__ . "/../yesticket_helpers.php");
include_once("settings_base.php");

class YesTicketSettingsTechnical extends YesTicketSettingsBase
{

  /**
   * Constructor.
   *
   * @param string $template_path
   */
  public function __construct($parent_slug, $template_path)
  {
    parent::__construct($parent_slug, $template_path);
    $this->configure();
  }

  /**
   * Configure the admin page using the Settings API.
   */
  public function configure()
  {

    YesTicketPluginOptions::getInstance()->register_settings_technical($this->get_slug());

    // Register technical section and fields
    add_settings_section(
      $this->get_slug(),
      __("Technical Settings", "yesticket"),
      array($this, 'render_technical_section'),
      $this->get_slug()
    );
    add_settings_field(
      $this->get_slug() . '-cache_time',
      __("Cache time in minutes", "yesticket"),
      array($this, 'render_cache_time'),
      $this->get_slug(),
      $this->get_slug()
    );
  }

  /**
   * Get the slug used by the admin page.
   *
   * @return string
   */
  public function get_slug()
  {
    return $this->get_parent_slug() . '-technical';
  }

  /**
   * Render the settings page.
   */
  public function render()
  {
    $action = esc_url(admin_url('options.php'));
    $this->render_template('settings_technical', compact("action"));
  }

  /**
   * Renders the cache_time field.
   */
  public function render_cache_time()
  {
    $cache_time = YesTicketPluginOptions::getInstance()->getCacheTimeInMinutes();
    print <<<EOD
        <input type='number' 
               name='yesticket_settings_technical[cache_time_in_minutes]' 
               min="0" 
               step="1" 
               value='$cache_time'>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  public function render_clear_cache_button()
  {
    /* translators: The sentence ends with a button 'Clear Cache' (can be translated at that msgId) */
    $hint_text = __("If your changes in YesTicket are not reflected fast enough, try to: ", "yesticket");
    /* translators: Text on a button, use imperativ if possible. */
    $button_text = __("Clear Cache", "yesticket");
    $pageQuery = $this->get_parent_slug();
    print <<<EOD
      <form action="admin.php?page=$pageQuery" method="POST">
        <input type="hidden" name="clear-cache" value="1">
        <label for="clear-cache_submit">$hint_text</label>
        <input type="submit" name="clear-cache_submit" value="$button_text">
      </form>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Render the technical settings section of the plugin's admin page.
   */
  public function render_technical_section()
  {
    _e("Change these settings at your own risk.", "yesticket");
  }

  private function ytp_clear_cache()
  {
    $cacheKeys = get_option('yesticket_transient_keys');
    update_option('yesticket_transient_keys', array());
    foreach ($cacheKeys as $k) {
      delete_transient($k);
    }
    ytp_log(__FILE__ . "@" . __LINE__ . ": 'Clearing Cache, triggered by user.'");
    echo $this->success_message(
      /* translators: Success Message after clearing cache */
      __("Deleted the cache.", "yesticket")
    );
  }

  public function feedback()
  {
    if (isset($_POST['clear-cache'])) {
      $this->ytp_clear_cache();
    }
  }

}
