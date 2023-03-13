<?php

namespace YesTicket;

include_once(__DIR__ . "/../../utility.php");

class TemplateSettingsRequiredTest extends \YTP_TemplateTestCase
{

  function test_html()
  {
    $this->expectTranslate("You need two things: your personal <b>organizer-ID</b> and the corresponding <b>Key</b>. Both can be found in your admin area on YesTicket > Marketing > Integrations:");
    $this->expectTranslate("https://www.yesticket.org/login/en/integration.php#wp-plugin");
    $asXML = $this->includeTemplate(__FILE__);
    $ariaXML = $asXML->xpath("//a")[0];
    $this->assertStringContainsString("https://www.yesticket.org/login", $ariaXML->xpath("@href")[0], "Should contain link to YesTicket Login Area.");
    $this->assertStringContainsString("_blank", $ariaXML->xpath("@target")[0], "Link should open in new tab.");
  }

}
