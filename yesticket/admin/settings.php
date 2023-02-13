<?php

include_once(__DIR__ . "/../yesticket_helpers.php");

class YesTicketSettings
{

  /**
   * Path to the example templates.
   *
   * @var string
   */
  private $template_path;

  /**
   * Slug of the parent menu entry
   *
   * @var string
   */
  private $parent_slug;

  /**
   * Constructor.
   *
   * @param string $template_path
   */
  public function __construct($parent_slug, $template_path)
  {
    $this->parent_slug = $parent_slug;
    $this->template_path = rtrim($template_path, '/');
  }

  /**
   * Configure the admin page using the Settings API.
   */
  public function configure()
  {
    /// Register settings
    $settings_args = array(
      'type' => 'object',
      'default' => array(
        'cache_time_in_minutes' => 60,
        'yesticket_transient_keys' => array(),
        'organizer_id' => NULL,
        'api_key' => NULL,
      ),
    );
    register_setting($this->get_slug(), 'yesticket_settings', $settings_args);

    // Register required section and fields
    add_settings_section(
      $this->get_slug() . '-required',
      __("Required Settings", "yesticket"),
      array($this, 'render_required_section'),
      $this->get_slug()
    );
    add_settings_field(
      $this->get_slug() . '-organizer_id',
      /* translators: Please keep the quotation marks! */
      __("Your 'organizer-ID'", "yesticket"),
      array($this, 'render_organizer_id'),
      $this->get_slug(),
      $this->get_slug() . '-required'
    );
    add_settings_field(
      $this->get_slug() . '-api_key',
      /* translators: Please keep the quotation marks! */
      __("Your 'key'", "yesticket"),
      array($this, 'render_api_key'),
      $this->get_slug(),
      $this->get_slug() . '-required'
    );
    // Register technical section and fields
    add_settings_section(
      $this->get_slug() . '-technical',
      __("Technical Settings", "yesticket"),
      array($this, 'render_technical_section'),
      $this->get_slug()
    );
    add_settings_field(
      $this->get_slug() . '-cache_time',
      __("Cache time in minutes", "yesticket"),
      array($this, 'render_cache_time'),
      $this->get_slug(),
      $this->get_slug() . '-technical'
    );
  }

  /**
   * Get the capability required to view the admin page.
   *
   * @return string
   */
  public function get_capability()
  {
    return 'install_plugins';
  }

  /**
   * Get the title of the admin page in the WordPress admin menu.
   *
   * @return string
   */
  public function get_menu_title()
  {
    return __("Settings");
  }

  /**
   * Get the title of the admin page.
   *
   * @return string
   */
  public function get_page_title()
  {
    return 'YesTicket ' . __("Settings");
  }

  /**
   * Get the parent slug of the admin page.
   *
   * @return string
   */
  public function get_parent_slug()
  {
    return $this->parent_slug;
  }

  /**
   * Get the slug used by the admin page.
   *
   * @return string
   */
  public function get_slug()
  {
    return $this->parent_slug . '-settings';
  }

  /**
   * Renders the api_key field.
   */
  public function render_api_key()
  {
    $options = get_option('yesticket_settings');
    $api_key = $options['api_key'];
    print <<<EOD
        <input type='text'
               placeholder='61dc12e43225e22add15ff1b'
               name='yesticket_settings[api_key]'
               value='$api_key'>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Renders the organizer_id field.
   */
  public function render_organizer_id()
  {
    $options = get_option('yesticket_settings');
    $organizer_id = $options['organizer_id'];
    print <<<EOD
      <input type='number'
             min='1'
             step='1'
             name='yesticket_settings[organizer_id]'
             value='$organizer_id'>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Renders the cache_time field.
   */
  public function render_cache_time()
  {
    $options = get_option('yesticket_settings');
    $cache_time = $options['cache_time_in_minutes'];
    print <<<EOD
        <input type='number' 
               name='yesticket_settings[cache_time_in_minutes]' 
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
    $pageQuery = $this->get_slug();
    print <<<EOD
      <form action="admin.php?page=$pageQuery" method="POST">
        <input type="hidden" name="clear_cache" value="1">
        <label for="clear_cache_submit">$hint_text</label>
        <input type="submit" name="clear_cache_submit" value="$button_text">
      </form>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Render the settings page.
   */
  public function render_page()
  {
    $this->render_template('header');
    $this->render_settings_page();
  }

  /**
   * Render the settings page.
   */
  public function render_settings_page()
  {
    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : null;
    $action = esc_url(add_query_arg('tab', $activeTab, admin_url('options.php')));
    $this->render_template('settings', compact("action"));
  }

  /**
   * Render the required settings section of the plugin's admin page.
   */
  public function render_required_section()
  {
    ytp_log(__FILE__ . "@" . __LINE__ . ": 'req sec'");
    $this->render_template('required_settings');
  }

  /**
   * Render the technical settings section of the plugin's admin page.
   */
  public function render_technical_section()
  {
    $this->render_template('technical_section');
  }

  private function ytp_clear_cache()
  {
    $cacheKeys = get_option('yesticket_transient_keys');
    update_option('yesticket_transient_keys', array());
    foreach ($cacheKeys as $k) {
      delete_transient($k);
    }
    return $this->success_message(
      /* translators: Success Message after clearing cache */
      __("Deleted the cache.", "yesticket")
    );
  }

  public function feedback()
  {
    if (isset($_POST['clear_cache'])) {
      return $this->ytp_clear_cache();
    }
    if (isset($_GET['settings-updated'])) {
      return $this->success_message(
        /* translators: Success Message after updating settings */
        __("Settings saved.", "yesticket")
      );
    }
    return "<!-- no feedback -->";
  }

  private function success_message($msg)
  {
    return "<p style='background-color: #97ff00; padding: 1rem'>$msg</p>";
  }

  /**
   * Renders the given template if it's readable.
   *
   * @param string $template
   */
  private function render_template($template, $variables = array())
  {
    $template_path = $this->template_path . '/' . $template . '.php';

    if (!is_readable($template_path)) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": 'Template not found: $template_path'");
      return;
    }
    // Extract the variables to a local namespace
    extract($variables);

    include $template_path;
  }
}
