<?php

namespace YesTicket;

use YesTicket\Admin\SettingsRequired;

include_once(__DIR__ . "/../utility.php");

class SettingsRequiredTest extends \YTP_TranslateTestCase
{

  /**
   * @covers YesTicket\Admin\SettingsRequired
   */
  function test_render()
  {
    // Init Object
    $settingsTechnical = new SettingsRequired('yesticket-settings');
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
    $this->assertStringContainsString("wp-admin/admin.php?page=yesticket-settings", $hiddenInput->xpath("@value")[0]);
    // _wpnonce
    $hiddenInput = $formXML->xpath("input[@name='_wpnonce']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertNotEmpty($hiddenInput->xpath("@value")[0]);
    // Submit BTN
    $this->assertNotEmpty($formXML->xpath("//input[@type='submit']"), "Form must have a submit input.");
  }

}
