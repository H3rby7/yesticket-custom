<?php

namespace YesTicket;

use YesTicket\Admin\SettingsRequired;

include_once(__DIR__ . "/../utility.php");

class SettingsRequiredTest extends \YTP_TranslateTestCase
{

  /**
   * @covers YesTicket\Admin\SettingsRequired
   */
  function test_render_necessarySettingsNotSet()
  {
    // Init Object
    $settingsTechnical = new SettingsRequired('yesticket-settings');
    $_SERVER['REQUEST_URI'] = "http://example.org/wp-admin/admin.php?page=yesticket-settings";
    // Expect Translations
    $this->expectTranslate("Required Settings");
    $this->expectTranslate("You need two things: your personal <b>organizer-ID</b> and the corresponding <b>Key</b>. Both can be found in your admin area on YesTicket > Marketing > Integrations:");
    $this->expectTranslate("https://www.yesticket.org/login/en/integration.php#wp-plugin");
    $this->expectTranslate("Your 'key'");
    $this->expectTranslate("Your 'organizer-ID'");
    $this->expectTranslate("Save Changes", "default");
    // Render
    \ob_start();
    $settingsTechnical->render();
    $result = \ob_get_clean();
    $this->assertNotEmpty($result);
    $asXML = $this->validateAndGetAsXml($result);
    // Settings Form Assertions
    $formXML = $asXML->xpath("//form[@method='post']")[0];
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
    $this->assertStringContainsString("wp-admin/admin.php?page=yesticket", $hiddenInput->xpath("@value")[0]);
    // _wpnonce
    $hiddenInput = $formXML->xpath("input[@name='_wpnonce']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertNotEmpty($hiddenInput->xpath("@value")[0]);
    // Submit BTN
    $this->assertNotEmpty($formXML->xpath("//input[@type='submit']"), "Form must have a submit input.");
  }

}
