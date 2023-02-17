<?php
if (!function_exists('is_countable')) {
  function is_countable($var)
  {
    return (is_array($var) || $var instanceof Countable);
  }
}

function ytp_getImageUrl($fileName)
{
  return plugin_dir_url(__FILE__) . 'img/' . $fileName;
}

if (!function_exists('ytp_log')) {

  function ytp_log($log)
  {
    if (true === WP_DEBUG) {
      if (is_array($log) || is_object($log)) {
        error_log("YESTICKET: " . print_r($log, true));
      } else {
        error_log("YESTICKET: " . $log);
      }
    }
  }
}

class YesTicketPluginOptions
{
  static private $instance;
  private const SETTINGS_TECHNICAL_KEY = 'yesticket_settings_technical';
  private const SETTINGS_REQUIRED_KEY = 'yesticket_settings_required';

  static public function getInstance()
  {
    if (!isset(YesTicketPluginOptions::$instance)) {
      YesTicketPluginOptions::$instance = new YesTicketPluginOptions();
    }
    return YesTicketPluginOptions::$instance;
  }

  /// Register settings
  private $settings_required_args = array(
    'type' => 'object',
    'default' => array(
      'organizer_id' => NULL,
      'api_key' => NULL,
    ),
  );

  private $settings_technical_args = array(
    'type' => 'object',
    'default' => array(
      'cache_time_in_minutes' => 60
    ),
  );

  private function getOptionString($object_name, $option_key, $default)
  {
    $options = get_option($object_name);
    if (!$options) {
      return $default;
    }
    if (empty($options[$option_key])) {
      return $default;
    }
    return $options[$option_key];
  }

  private function getOptionNumber($object_name, $option_key, $default)
  {
    $options = get_option($object_name);
    if (!$options) {
      return $default;
    }
    if (!is_numeric($options[$option_key])) {
      return $default;
    }
    return $options[$option_key];
  }

  public function register_settings_technical($slug)
  {
    register_setting(
      $slug,
      YesTicketPluginOptions::SETTINGS_TECHNICAL_KEY,
      $this->settings_technical_args
    );
  }

  public function register_settings_required($slug)
  {
    register_setting(
      $slug,
      YesTicketPluginOptions::SETTINGS_REQUIRED_KEY,
      $this->settings_required_args
    );
  }

  public function getOrganizerID()
  {
    return $this->getOptionNumber(
      YesTicketPluginOptions::SETTINGS_REQUIRED_KEY,
      'organizer_id',
      $this->settings_required_args['default']['organizer_id']
    );
  }

  public function getApiKey()
  {
    return $this->getOptionString(
      YesTicketPluginOptions::SETTINGS_REQUIRED_KEY,
      'api_key',
      $this->settings_required_args['default']['api_key']
    );
  }

  public function getCacheTimeInMinutes()
  {
    return $this->getOptionNumber(
      YesTicketPluginOptions::SETTINGS_TECHNICAL_KEY,
      'cache_time_in_minutes',
      $this->settings_technical_args['default']['cache_time_in_minutes']
    );
  }

  public function areNecessarySettingsSet() {
    $organizer_id = $this->getOrganizerID();
    $api_key = $this->getApiKey();
    if ($organizer_id === null || trim($organizer_id) === '') {
      return false;
    }
    if ($api_key === null || trim($api_key) === '') {
      return false;
    }
    return true;
  }
}
