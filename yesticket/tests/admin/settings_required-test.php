<?php

namespace YesTicket;

use \SimpleXMLElement;
use \YTP_TranslateTestCase;
use \YesTicket\Admin\SettingsRequired;
use \YesTicket\PluginOptions;

include_once(__DIR__ . "/../utility.php");

class SettingsRequiredTest extends YTP_TranslateTestCase
{

  public function setUp(): void
  {
    parent::setUp();
    $this->expectTranslate("Required Settings");
    $this->expectTranslate("Your 'key'");
    $this->expectTranslate("Your 'organizer-ID'");
  }

  function test_render_necessarySettingsNotSet()
  {
    // Init Object
    $settingsRequired = new SettingsRequired('yesticket-settings');
    // Init Options
    \delete_option(PluginOptions::SETTINGS_REQUIRED_KEY);
    // Change server Context
    $_SERVER['REQUEST_URI'] = "http://example.org/wp-admin/admin.php?page=yesticket-settings";
    // Expect Translations
    $this->expectTranslate("You need two things: your personal <b>organizer-ID</b> and the corresponding <b>Key</b>. Both can be found in your admin area on YesTicket > Marketing > Integrations:");
    $this->expectTranslate("https://www.yesticket.org/login/en/integration.php#wp-plugin");
    $this->expectTranslate("Save Changes", "default");
    // Render
    \ob_start();
    $settingsRequired->render();
    $result = \ob_get_clean();
    $this->assertNotEmpty($result);
    $asXML = $this->validateAndGetAsXml($result);
    // Settings Form Assertions
    $formXML = $asXML->xpath("//form[@method='post']")[0];
    $this->makeFormAssertions($formXML, "wp-admin/admin.php?page=yesticket");
  }

  function test_render_necessarySettingsPresent()
  {
    // Init Object
    $settingsRequired = new SettingsRequired('yesticket-settings');
    // Init Options
    \update_option(PluginOptions::SETTINGS_REQUIRED_KEY, array(
      'organizer_id' => "1",
      'api_key' => "an-api-key",
    ));
    // Change server Context
    $_SERVER['REQUEST_URI'] = "http://example.org/wp-admin/admin.php?page=yesticket-settings";
    // Expect Translations
    $this->expectTranslate("You need two things: your personal <b>organizer-ID</b> and the corresponding <b>Key</b>. Both can be found in your admin area on YesTicket > Marketing > Integrations:");
    $this->expectTranslate("https://www.yesticket.org/login/en/integration.php#wp-plugin");
    $this->expectTranslate("Save Changes", "default");
    // Render
    \ob_start();
    $settingsRequired->render();
    $result = \ob_get_clean();
    $this->assertNotEmpty($result);
    $asXML = $this->validateAndGetAsXml($result);
    // Settings Form Assertions
    $formXML = $asXML->xpath("//form[@method='post']")[0];
    $this->makeFormAssertions($formXML, "wp-admin/admin.php?page=yesticket-settings");
  }

  function test_render_feedback_update_no()
  {
    $settingsRequired = new SettingsRequired('yesticket-settings');
    unset($_GET['settings-updated']);
    $result = $settingsRequired->feedback();
    $this->assertEmpty($result);
  }

  function test_render_feedback_update_yes()
  {
    $settingsRequired = new SettingsRequired('yesticket-settings');
    $_GET['settings-updated'] = "yes";
    // Expect Translations
    $expected_text = $this->expectTranslate("Settings saved.");
    $result = $settingsRequired->feedback();
    $this->assertNotEmpty($result);
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlContainsText($expected_text, $asXML);
  }

  /**
   * @param SimpleXMLElement $formXML the form
   * @param string $referer to expect for _wp_http_referer input field.
   */
  function makeFormAssertions($formXML, $referer)
  {
    // Form element itself
    $this->assertNotEmpty($formXML, "Should have a form with method='post'.");
    $this->assertStringContainsString("wp-admin/options.php", $formXML->xpath("@action")[0]);
    // option_page
    $hiddenInput = $formXML->xpath("input[@name='option_page']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertStringContainsString("yesticket-settings-required", $hiddenInput->xpath("@value")[0]);
    // action
    $hiddenInput = $formXML->xpath("input[@name='action']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    // _wp_http_referer
    $hiddenInput = $formXML->xpath("input[@name='_wp_http_referer']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertStringContainsString($referer, $hiddenInput->xpath("@value")[0]);
    // _wpnonce
    $hiddenInput = $formXML->xpath("input[@name='_wpnonce']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertNotEmpty($hiddenInput->xpath("@value")[0]);
    // Submit BTN
    $this->assertNotEmpty($formXML->xpath("//input[@type='submit']"), "Form must have a submit input.");
  }
}
