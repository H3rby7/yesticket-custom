<?php

namespace YesTicket;

include_once(__DIR__ . "/../../utility.php");

class TemplateAdminHeaderTest extends \YTP_TemplateTestCase
{

  function test_html()
  {
    $this->expectTranslate("YesTicket is a ticketing system and we love wordpress - so here's our plugin! You can integrate upcoming events and audience feedback (testimonials) using shortcodes anywhere on your page. Be it pages, posts, widgets, ... get creative!");
    $asXML = $this->includeTemplate(__FILE__);
    $this->assertNotEmpty($asXML->xpath("/*[@class='ytp-admin']"), "Root element should have 'ytp-admin' css class.");
    $this->assertStringContainsString("YesTicket_logo.png", $asXML->xpath("//img/@src")[0], "Should contain YesTicket logo somewhere.");
    $this->assertNotEmpty($asXML->xpath("//h1"), "Should have <h1> element.");
    $this->assertNotEmpty($asXML->xpath("//p"), "Should have <p> element");
  }

}
