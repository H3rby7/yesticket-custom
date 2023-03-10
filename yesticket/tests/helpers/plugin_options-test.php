<?php

namespace YesTicket;

class PluginOptionsTest extends \WP_UnitTestCase
{
  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\PluginOptions"));
  }

  /**
   * @covers YesTicket\PluginOptions
   */
  function test_get_instance()
  {
    $_class = new \ReflectionClass(PluginOptions::class);
    $_instance_prop = $_class->getProperty("instance");
    $_instance_prop->setAccessible(true);
    $_instance_prop->setValue(NULL);
    $this->assertNotEmpty(PluginOptions::getInstance());
    $_instance_prop->setAccessible(false);
  }

  /**
   * Helper function, runs the register_settings_technical function
   * 
   * @param string $opt_group
   */
  private function register_settings_technical($opt_group = 'test-slug')
  {
    PluginOptions::getInstance()->register_settings_technical($opt_group);
  }

  /**
   * Helper function, runs the register_settings_technical function
   * 
   * @param string $opt_group
   */
  private function register_settings_required($opt_group = 'test-slug')
  {
    PluginOptions::getInstance()->register_settings_required($opt_group);
  }

  /**
   * @covers YesTicket\PluginOptions
   */
  function test_register_settings_technical()
  {
    // Given
    $opt_group = 'test-slug';
    $opt_key = 'yesticket_settings_technical';

    // Make sure we start clear
    global $new_allowed_options, $wp_registered_settings;
    unset( $new_allowed_options[ $opt_group ] );
    unset( $wp_registered_settings[ $opt_key ] );

    // Register Settings
    $this->register_settings_technical($opt_group);
    $option = \get_option($opt_key);

    // Assertions
    $this->assertNotEmpty($option);
    $this->assertCount(1, $option);

    $this->assertArrayHasKey('cache_time_in_minutes', $option);
    $this->assertSame(60, $option['cache_time_in_minutes']);

    // Clean up
    \unregister_setting($opt_group, $opt_key);

    // Ensure cleaned
    $this->assertEmpty(\get_option($opt_key));
  }

  /**
   * @covers YesTicket\PluginOptions
   */
  function test_register_settings_required()
  {
    // Given
    $opt_group = 'test-slug';
    $opt_key = 'yesticket_settings_required';

    // Make sure we start clear
    global $new_allowed_options, $wp_registered_settings;
    unset( $new_allowed_options[ $opt_group ] );
    unset( $wp_registered_settings[ $opt_key ] );

    // Register Settings
    $this->register_settings_required($opt_group);
    $option = \get_option($opt_key);

    // Assertions
    $this->assertNotEmpty($option);
    $this->assertCount(2, $option);

    $this->assertArrayHasKey('organizer_id', $option);
    $this->assertArrayHasKey('api_key', $option);
    $this->assertNull($option['organizer_id']);
    $this->assertNull($option['api_key']);

    // Clean up
    \unregister_setting($opt_group, $opt_key);

    // Ensure cleaned
    $this->assertEmpty(\get_option($opt_key));
  }

  /**
   * @covers YesTicket\PluginOptions
   */
  function test_getCacheTimeInMinutes()
  {
    // Setup
    $this->register_settings_technical();

    // Basic Call
    $this->assertSame(60, PluginOptions::getInstance()->getCacheTimeInMinutes(), "Expected option's default value");

    // Test update propagates
    $this->assertTrue(\update_option('yesticket_settings_technical', array(
      'cache_time_in_minutes' => 69
    )));
    $this->assertSame(69, PluginOptions::getInstance()->getCacheTimeInMinutes(), "Value should have changed");
  }

  /**
   * @covers YesTicket\PluginOptions
   */
  function test_getApiKey()
  {
    // Setup
    $opt_key = 'yesticket_settings_required';
    $this->register_settings_required();

    // Basic Call
    $this->assertNull(PluginOptions::getInstance()->getApiKey(), "Expected option's default value");

    // Test update propagates
    $this->assertTrue(\update_option($opt_key, array(
      'api_key' => 'testkey'
    )));
    $this->assertSame('testkey', PluginOptions::getInstance()->getApiKey(), "Value should have changed");
  }

  /**
   * @covers YesTicket\PluginOptions
   */
  function test_getOrganizerID()
  {
    // Setup
    $opt_key = 'yesticket_settings_required';
    $this->register_settings_required();

    // Basic Call
    $this->assertNull(PluginOptions::getInstance()->getOrganizerID(), "Expected option's default value");

    // Test update propagates
    $this->assertTrue(\update_option($opt_key, array(
      'organizer_id' => '161'
    )));
    $this->assertSame('161', PluginOptions::getInstance()->getOrganizerID(), "Value should have changed");
  }

  /**
   * @covers YesTicket\PluginOptions
   */
  function test_areNecessarySettingsSet()
  {
    // Given
    $opt_group = 'test-slug';
    $opt_key = 'yesticket_settings_required';

    // Make sure we start clear
    \unregister_setting($opt_group, $opt_key);

    // Settings not registered, expect false
    $this->assertFalse(PluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because settings were not registered.");

    // Register settings AND params are NULL, expect false
    $this->register_settings_required($opt_group);
    $this->assertFalse(PluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because organizer_id and api_key are NULL.");

    // only organizer_id is set, expect false
    $this->assertTrue(\update_option($opt_key, array(
      'organizer_id' => '161'
    )));
    $this->assertFalse(PluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because api_key is NULL.");

    // only api_key is set, expect false
    $this->assertTrue(\update_option($opt_key, array(
      'api_key' => 'testkey'
    )));
    $this->assertFalse(PluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because organizer_id is NULL.");

    // All set, expect true
    $this->assertTrue(\update_option($opt_key, array(
      'organizer_id' => '161',
      'api_key' => 'testkey'
    )));
    $this->assertTrue(PluginOptions::getInstance()->areNecessarySettingsSet());
  }
}
