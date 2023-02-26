<?php

class YesTicketHelpersTest extends WP_UnitTestCase
{
  /**
   * @covers ::ytp_getImageUrl
   */
  function test_ytp_getImageUrl()
  {
    $this->assertSame('http://example.org/wp-content/plugins/app/yesticket/src/img/', ytp_getImageUrl(''));
    $this->assertSame('http://example.org/wp-content/plugins/app/yesticket/src/img/my-image.png', ytp_getImageUrl('my-image.png'));
  }

  /**
   * @covers ::ytp_render_no_events
   */
  function test_ytp_render_no_events()
  {
    $text = ytp_render_no_events();
    libxml_clear_errors();
    simplexml_load_string($text);
    $this->assertCount(0, libxml_get_errors(), 'Should produce valid HTML');
  }

  /**
   * @covers ::ytp_render_no_testimonials
   */
  function test_ytp_render_no_testimonials()
  {
    $text = ytp_render_no_testimonials();
    libxml_clear_errors();
    simplexml_load_string($text);
    $this->assertCount(0, libxml_get_errors(), 'Should produce valid HTML');
  }

  /**
   * @covers ::ytp_log
   */
  function test_ytp_log_string_expecting_string()
  {
    $errorLogTmpfile = tmpfile();
    $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpfile)['uri']);
    ytp_log("my log content");
    ini_set('error_log', $errorLogLocationBackup);
    $result = stream_get_contents($errorLogTmpfile);
    $this->assertStringContainsString("YESTICKET: my log content", $result);
  }

  /**
   * @covers ::ytp_log
   */
  function test_ytp_log_array_expecting_serialized_string()
  {
    $errorLogTmpfile = tmpfile();
    $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpfile)['uri']);
    ytp_log(array("my-key" => "my-value"));
    ini_set('error_log', $errorLogLocationBackup);
    $result = stream_get_contents($errorLogTmpfile);
    $this->assertStringContainsString('YESTICKET: Array', $result);
    $this->assertStringContainsString('[my-key] => my-value', $result);
  }

  function run_ytp_render_eventType($expected, $input)
  {
    ob_start();
    ytp_render_eventType($input);
    $this->assertSame($expected, ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers ::ytp_render_eventType
   */
  function test_ytp_render_eventType()
  {
    add_filter('locale', function () {
      return 'en_EN';
    });
    $this->run_ytp_render_eventType("Performance", "auftritt");
    $this->run_ytp_render_eventType("Performance", "Auftritt");
    $this->run_ytp_render_eventType("Workshop", "workshop");
    $this->run_ytp_render_eventType("Workshop", "Workshop");
    $this->run_ytp_render_eventType("Festival", "festival");
    $this->run_ytp_render_eventType("Festival", "Festival");
    $this->run_ytp_render_eventType("unknownEventType", "unknownEventType");
  }
}
