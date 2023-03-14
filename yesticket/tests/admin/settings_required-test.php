<?php

namespace YesTicket;

use YesTicket\Admin\SettingsRequired;

include_once(__DIR__ . "/../utility.php");

class SettingsRequiredTest extends \WP_UnitTestCase
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
    \libxml_clear_errors();
    $asXML = \simplexml_load_string(\closeStandaloneHtmlTags($result));
    $formXML = $asXML->xpath("//form[@method='post']")[0];
    // Run assertions on XML
    $this->assertEmpty(libxml_get_errors(), "Should produce valid HTML, but is: >>> \n" . $asXML->asXML());
    // Settings Form Assertions
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
