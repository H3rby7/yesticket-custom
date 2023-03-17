<?php

namespace YesTicket\Admin;

use \YesTicket\PluginOptions;

include_once("settings_section.php");
include_once(__DIR__ . "/../helpers/plugin_options.php");

/**
 * YesTicket's required settings
 */
class SettingsRequired extends SettingsSection
{

  public function __construct($parent_slug)
  {
    parent::__construct($parent_slug);
    $this->configure();
  }

  /**
   * Configure the required settings using the Settings API.
   */
  public function configure()
  {

    PluginOptions::getInstance()->register_settings_required($this->get_slug());

    // Register required section and fields
    \add_settings_section(
      $this->get_slug(),
      __("Required Settings", "yesticket"),
      array($this, 'render_heading'),
      $this->get_slug()
    );
    \add_settings_field(
      $this->get_slug() . '-organizer_id',
      /* translators: Please keep the quotation marks! */
      __("Your 'organizer-ID'", "yesticket"),
      array($this, 'render_organizer_id'),
      $this->get_slug(),
      $this->get_slug()
    );
    \add_settings_field(
      $this->get_slug() . '-api_key',
      /* translators: Please keep the quotation marks! */
      __("Your 'key'", "yesticket"),
      array($this, 'render_api_key'),
      $this->get_slug(),
      $this->get_slug()
    );
  }

  /**
   * Get the slug used by the required settings page.
   *
   * @return string
   */
  public function get_slug()
  {
    return $this->get_parent_slug() . '-required';
  }

  /**
   * Prints the required settings.
   * If necessary settings are not yet set, changes slug to the examples page, to redirect there right after these settings being set.
   */
  public function render()
  {
    $action = \esc_url(\admin_url('options.php'));
    $request_url   = \remove_query_arg('_wp_http_referer');
    if (!PluginOptions::getInstance()->areNecessarySettingsSet()) {
      // TODO: If we redirect to examples page, we might redirect although the settings are not set and the user lands on an error page.
      // reproduce: visit the site without necessary settings set; update only one option.
      $request_url = \preg_replace('/-settings/', '', $request_url);
    }
    $this->render_template('settings_required', \compact("action", "request_url"));
  }

  /**
   * Prints the api_key field.
   */
  public function render_api_key()
  {
    $api_key = PluginOptions::getInstance()->getApiKey();
    $opt_name = PluginOptions::SETTINGS_REQUIRED_KEY;
    print <<<EOD
        <input type='text'
               placeholder='61dc12e43225e22add15ff1b'
               name='${opt_name}[api_key]'
               value='$api_key'>
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Prints the organizer_id field.
   */
  public function render_organizer_id()
  {
    $organizer_id = PluginOptions::getInstance()->getOrganizerID();
    $opt_name = PluginOptions::SETTINGS_REQUIRED_KEY;
    print <<<EOD
      <input type='number'
             min='1'
             step='1'
             name='${opt_name}[organizer_id]'
             value='$organizer_id'>
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  /**
   * Prints the required settings section header.
   */
  public function render_heading()
  {
    $this->render_template('settings_required_heading');
  }

  /**
   * Return feedback from saving changes
   * 
   * @return string feedback as html elements
   */
  public function feedback()
  {
    if (isset($_GET['settings-updated'])) {
      return $this->success_message(
        /* translators: Success Message after updating settings */
        __("Settings saved.", "yesticket")
      );
    }
  }
}
