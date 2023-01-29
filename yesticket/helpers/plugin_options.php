<?php
include_once("functions.php");

function strpos_findLast_viaRegex($haystack, $needlePattern) {
  // https://www.php.net/manual/de/function.preg-match-all.php
  preg_match_all($needlePattern, $haystack, $matches, PREG_OFFSET_CAPTURE);
  // $matches is an array containing the matches
  // each match is an array of size 1, containing at [0] an array of 
  // [0] being the match and [1] the corresponding index.
  if (count($matches) < 0) {
    return false;
  }
  $lastMatch = $matches[count($matches) - 1];
  return $lastMatch[0][1];
}
/**
 * Manage the plugin's wp options
 */
class YesTicketPluginOptions
{
  /**
   * The $instance
   *
   * @var YesTicketCache
   */
  static private $instance;

  /**
   * Option_Name of the technical settings
   *
   * @var SETTINGS_TECHNICAL_KEY
   */
  private const SETTINGS_TECHNICAL_KEY = 'yesticket_settings_technical';

  /**
   * Option_Name of the required settings
   *
   * @var SETTINGS_REQUIRED_KEY
   */
  private const SETTINGS_REQUIRED_KEY = 'yesticket_settings_required';

  /**
   * Get the $instance
   * 
   * @return YesTicketPluginOptions $instance
   */
  static public function getInstance()
  {
    if (!isset(YesTicketPluginOptions::$instance)) {
      YesTicketPluginOptions::$instance = new YesTicketPluginOptions();
    }
    return YesTicketPluginOptions::$instance;
  }

  /**
   * @var $settings_required_args Data used to describe the required settings when registered.
   */
  private $settings_required_args = array(
    'type' => 'object',
    'default' => array(
      'organizer_id' => NULL,
      'api_key' => NULL,
    ),
  );

  /**
   * @var $settings_technical_args Data used to describe the technical settings when registered.
   */
  private $settings_technical_args = array(
    'type' => 'object',
    'default' => array(
      'cache_time_in_minutes' => 60
    ),
  );

  /**
   * Get the option or the default as string
   * 
   * @param string $option_name of the containing option object
   * @param string $option_key to get the wanted attribute
   * @param string $default is returned in case the requested option was not set
   * 
   * @return string the option's content or the $default
   */
  private function getOptionString($option_name, $option_key, $default = NULL)
  {
    $options = get_option($option_name);
    if (!$options) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": '$option_name was not found, returning default: $default'");
      return $default;
    }
    if (!isset($options[$option_key]) || empty($options[$option_key])) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": '$option_name does not contain key $option_key, returning default: $default'");
      return $default;
    }
    return $options[$option_key];
  }

  /**
   * Get the option or the default as number
   * 
   * @param string $option_name of the containing option object
   * @param string $option_key to get the wanted attribute
   * @param string $default is returned in case the requested option was not set
   * 
   * @return number the option's content or the $default
   */
  private function getOptionNumber($option_name, $option_key, $default = NULL)
  {
    $options = get_option($option_name);
    if (!$options) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": '$option_name was not found, returning default: $default'");
      return $default;
    }
    if (!isset($options[$option_key]) || !is_numeric($options[$option_key])) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": '$option_name does not contain key $option_key, returning default: $default'");
      return $default;
    }
    return $options[$option_key];
  }

  /**
   * Use wp settings api to register technical settings
   * 
   * @param string $option_group
   */
  public function register_settings_technical($option_group)
  {
    register_setting(
      $option_group,
      YesTicketPluginOptions::SETTINGS_TECHNICAL_KEY,
      $this->settings_technical_args
    );
  }

  /**
   * Use wp settings api to register required settings
   * 
   * @param string $option_group
   */
  public function register_settings_required($option_group)
  {
    register_setting(
      $option_group,
      YesTicketPluginOptions::SETTINGS_REQUIRED_KEY,
      $this->settings_required_args
    );
  }

  /**
   * Get the organizer ID
   * 
   * @return number organizer_id or NULL
   */
  public function getOrganizerID()
  {
    return $this->getOptionNumber(
      YesTicketPluginOptions::SETTINGS_REQUIRED_KEY,
      'organizer_id'
    );
  }

  /**
   * Get the API key
   * 
   * @return string api_key or NULL
   */
  public function getApiKey()
  {
    return $this->getOptionString(
      YesTicketPluginOptions::SETTINGS_REQUIRED_KEY,
      'api_key'
    );
  }

  /**
   * Get the cache time in minutes
   * 
   * @return string cache time or default, see $settings_technical_args
   */
  public function getCacheTimeInMinutes()
  {
    return $this->getOptionNumber(
      YesTicketPluginOptions::SETTINGS_TECHNICAL_KEY,
      'cache_time_in_minutes'
    );
  }

  /**
   * Check if organizer_id and api_key have been configured.
   * 
   * @return boolean FALSE, if any necessary setting is missing; else TRUE
   */
  public function areNecessarySettingsSet()
  {
    $organizer_id = $this->getOrganizerID();
    $api_key = $this->getApiKey();
    if ($organizer_id === null) {
      return false;
    }
    if ($api_key === null || trim($api_key) === '') {
      return false;
    }
    return true;
  }
}

?>
