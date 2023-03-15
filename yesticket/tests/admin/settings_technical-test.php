<?php

namespace YesTicket;

use SimpleXMLElement;
use YesTicket\Admin\SettingsTechnical;

include_once(__DIR__ . "/../utility.php");

class SettingsTechnicalTest extends \YTP_TranslateTestCase
{
  
  function test_render()
  {
    // Init Object
    $settingsTechnical = new SettingsTechnical('yesticket-settings', null);
    $_SERVER['REQUEST_URI'] = "http://example.org/wp-admin/admin.php?page=yesticket-settings";
    $_GET['tab'] = "technical";
    // Expect Translations
    $this->expectTranslate("Technical Settings");
    $this->expectTranslate("Change these settings at your own risk.");
    $this->expectTranslate("Cache time in minutes");
    $this->expectTranslate("Save Changes", "default");
    $this->expectTranslate("If your changes in YesTicket are not reflected fast enough, try to: ");
    $this->expectTranslate("Clear Cache");
    // Render
    \ob_start();
    $settingsTechnical->render();
    $result = \ob_get_clean();
    $this->assertNotEmpty($result);
    $asXML = $this->validateAndGetAsXml($result);
    // Run assertions on XML
    $this->assertSettings($asXML->xpath("//form[@method='post']")[0]);
    $this->assertClearCache($asXML->xpath("//form[@method='post']")[1]);
  }

  /**
   * Run the assertions for the settings configuration form
   * 
   * @param SimpleXMLElement $formXML the <form></form>
   * @param string expected $action string for form's action attribute
   * @param string expected $request_url string for _wp_http_referer
   */
  function assertSettings($formXML)
  {
    // Form element itself
    $this->assertNotEmpty($formXML, "Settings should have a form with method='post'.");
    $this->assertStringContainsString("wp-admin/options.php", $formXML->xpath("@action")[0]);
    // option_page
    $hiddenInput = $formXML->xpath("input[@name='option_page']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertStringContainsString("yesticket-settings-technical", $hiddenInput->xpath("@value")[0]);
    // action
    $hiddenInput = $formXML->xpath("input[@name='action']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    // _wp_http_referer
    $hiddenInput = $formXML->xpath("input[@name='_wp_http_referer']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertStringContainsString("?", $hiddenInput->xpath("@value")[0]);
    // _wpnonce
    $hiddenInput = $formXML->xpath("input[@name='_wpnonce']")[0];
    $this->assertStringContainsString("hidden", $hiddenInput->xpath("@type")[0]);
    $this->assertNotEmpty($hiddenInput->xpath("@value")[0]);
    // Submit BTN
    $this->assertNotEmpty($formXML->xpath("//input[@type='submit']"), "Form must have a submit input.");
  }

  /**
   * Run the assertions for the clear cache form
   * 
   * @param SimpleXMLElement $formXML the <form></form>
   */
  function assertClearCache($formXML)
  {
    // Form element itself
    $this->assertNotEmpty($formXML, "Clear cache should have a form with method='post'.");
    $this->assertStringContainsString("admin.php?page=yesticket-settings&tab=technical", $formXML->xpath("@action")[0]);

    // Submit BTN
    $this->assertNotEmpty($formXML->xpath("//input[@type='submit']"), "Form must have a submit input.");
  }
}
