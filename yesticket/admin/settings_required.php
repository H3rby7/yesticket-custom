<?php

include_once(__DIR__ . "/../yesticket_helpers.php");
include_once("settings_base_class.php");

class YesTicketSettingsRequired extends YesTicketSettingsBase
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

    YesTicketPluginOptions::getInstance()->register_settings_required($this->get_slug());

    // Register required section and fields
    add_settings_section(
      $this->get_slug(),
      __("Required Settings", "yesticket"),
      array($this, 'render_heading'),
      $this->get_slug()
    );
    add_settings_field(
      $this->get_slug() . '-organizer_id',
      /* translators: Please keep the quotation marks! */
      __("Your 'organizer-ID'", "yesticket"),
      array($this, 'render_organizer_id'),
      $this->get_slug(),
      $this->get_slug()
    );
    add_settings_field(
      $this->get_slug() . '-api_key',
      /* translators: Please keep the quotation marks! */
      __("Your 'key'", "yesticket"),
      array($this, 'render_api_key'),
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
    return $this->get_parent_slug() . '-required';
  }

  /**
   * Renders the api_key field.
   */
  public function render_api_key()
  {
    $api_key = YesTicketPluginOptions::getInstance()->getApiKey();
    print <<<EOD
        <input type='text'
               placeholder='61dc12e43225e22add15ff1b'
               name='yesticket_settings_required[api_key]'
               value='$api_key'>
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Renders the organizer_id field.
   */
  public function render_organizer_id()
  {
    $organizer_id = YesTicketPluginOptions::getInstance()->getOrganizerID();
    print <<<EOD
      <input type='number'
             min='1'
             step='1'
             name='yesticket_settings_required[organizer_id]'
             value='$organizer_id'>
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Render the settings page.
   */
  public function render()
  {
    $action = esc_url(admin_url('options.php'));
    $this->render_template('settings_required', compact("action"));
  }

  /**
   * Render the required settings section of the plugin's admin page.
   */
  public function render_heading()
  {
    $this->render_template('settings_required_heading');
  }

  public function feedback()
  {
    if (isset($_GET['settings-updated'])) {
      echo $this->success_message(
        /* translators: Success Message after updating settings */
        __("Settings saved.", "yesticket")
      );
    }
  }
}
