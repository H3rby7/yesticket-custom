<?php

class YesTicketPluginOptionsTest extends WP_UnitTestCase
{
  function test_class_exists()
  {
    $this->assertTrue(class_exists("YesTicketPluginOptions"));
  }

  /**
   * @covers YesTicketPluginOptions::getInstance
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(YesTicketPluginOptions::getInstance());
  }

  /**
   * Helper function, runs the register_settings_technical function
   * 
   * @param string $opt_group
   */
  private function register_settings_technical($opt_group = 'test-slug')
  {
    YesTicketPluginOptions::getInstance()->register_settings_technical($opt_group);
  }

  /**
   * Helper function, runs the register_settings_technical function
   * 
   * @param string $opt_group
   */
  private function register_settings_required($opt_group = 'test-slug')
  {
    YesTicketPluginOptions::getInstance()->register_settings_required($opt_group);
  }

  /**
   * @covers YesTicketPluginOptions::register_settings_technical
   */
  function test_register_settings_technical()
  {
    // Given
    $opt_group = 'test-slug';
    $opt_key = 'yesticket_settings_technical';

    // Make sure we start clear
    unregister_setting($opt_group, $opt_key);

    // Register Settings
    $this->register_settings_technical($opt_group);
    $option = get_option($opt_key);

    // Assertions
    $this->assertNotEmpty($option);
    $this->assertCount(1, $option);

    $this->assertNotEmpty($option['cache_time_in_minutes']);
    $this->assertSame(60, $option['cache_time_in_minutes']);

    // Clean up
    unregister_setting($opt_group, $opt_key);

    // Ensure cleaned
    $this->assertEmpty(get_option($opt_key));
  }

  /**
   * @covers YesTicketPluginOptions::register_settings_required
   */
  function test_register_settings_required()
  {
    // Given
    $opt_group = 'test-slug';
    $opt_key = 'yesticket_settings_required';

    // Make sure we start clear
    unregister_setting($opt_group, $opt_key);

    // Register Settings
    $this->register_settings_required($opt_group);
    $option = get_option($opt_key);

    // Assertions
    $this->assertNotEmpty($option);
    $this->assertCount(2, $option);

    $this->assertNull($option['organizer_id']);
    $this->assertNull($option['api_key']);

    // Clean up
    unregister_setting($opt_group, $opt_key);

    // Ensure cleaned
    $this->assertEmpty(get_option($opt_key));
  }

  /**
   * @covers YesTicketPluginOptions::getCacheTimeInMinutes
   */
  function test_getCacheTimeInMinutes()
  {
    // Setup
    $this->register_settings_technical();

    // Basic Call
    $this->assertSame(60, YesTicketPluginOptions::getInstance()->getCacheTimeInMinutes(), "Expected option's default value");

    // Test update propagates
    $this->assertTrue(update_option('yesticket_settings_technical', array(
      'cache_time_in_minutes' => 69
    )));
    $this->assertSame(69, YesTicketPluginOptions::getInstance()->getCacheTimeInMinutes(), "Value should have changed");
  }

  /**
   * @covers YesTicketPluginOptions::getApiKey
   */
  function test_getApiKey()
  {
    // Setup
    $opt_key = 'yesticket_settings_required';
    $this->register_settings_required();

    // Basic Call
    $this->assertNull(YesTicketPluginOptions::getInstance()->getApiKey(), "Expected option's default value");

    // Test update propagates
    $this->assertTrue(update_option($opt_key, array(
      'api_key' => 'testkey'
    )));
    $this->assertSame('testkey', YesTicketPluginOptions::getInstance()->getApiKey(), "Value should have changed");
  }

  /**
   * @covers YesTicketPluginOptions::getOrganizerID
   */
  function test_getOrganizerID()
  {
    // Setup
    $opt_key = 'yesticket_settings_required';
    $this->register_settings_required();

    // Basic Call
    $this->assertNull(YesTicketPluginOptions::getInstance()->getOrganizerID(), "Expected option's default value");

    // Test update propagates
    $this->assertTrue(update_option($opt_key, array(
      'organizer_id' => '161'
    )));
    $this->assertSame('161', YesTicketPluginOptions::getInstance()->getOrganizerID(), "Value should have changed");
  }

  /**
   * @covers YesTicketPluginOptions::areNecessarySettingsSet
   */
  function test_areNecessarySettingsSet()
  {
    // Given
    $opt_group = 'test-slug';
    $opt_key = 'yesticket_settings_required';

    // Make sure we start clear
    unregister_setting($opt_group, $opt_key);

    // Settings not registered, expect false
    $this->assertFalse(YesTicketPluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because settings were not registered.");

    // Register settings AND params are NULL, expect false
    $this->register_settings_required($opt_group);
    $this->assertFalse(YesTicketPluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because organizer_id and api_key are NULL.");

    // only organizer_id is set, expect false
    $this->assertTrue(update_option($opt_key, array(
      'organizer_id' => '161'
    )));
    $this->assertFalse(YesTicketPluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because api_key is NULL.");

    // only api_key is set, expect false
    $this->assertTrue(update_option($opt_key, array(
      'api_key' => 'testkey'
    )));
    $this->assertFalse(YesTicketPluginOptions::getInstance()->areNecessarySettingsSet(), "Expect FALSE, because organizer_id is NULL.");

    // All set, expect true
    $this->assertTrue(update_option($opt_key, array(
      'organizer_id' => '161',
      'api_key' => 'testkey'
    )));
    $this->assertTrue(YesTicketPluginOptions::getInstance()->areNecessarySettingsSet());
  }
}
