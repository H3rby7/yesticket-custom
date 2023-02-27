<?php

include_once(__DIR__ . "/../utility.php");

class YesTicketHelpersTest extends WP_UnitTestCase
{

  /**
   * @covers ::ytp_getImageUrl
   */
  function test_ytp_getImageUrl()
  {
    $this->assertSame('http://example.org/wp-content/plugins/yesticket/src/img/', \ytp_getImageUrl(''));
    $this->assertSame('http://example.org/wp-content/plugins/yesticket/src/img/my-image.png', \ytp_getImageUrl('my-image.png'));
  }

  /**
   * @covers ::ytp_render_no_events
   */
  function test_ytp_render_no_events()
  {
    $text = \ytp_render_no_events();
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
   * @covers ::ytp_render_shortcode_container_div
   */
  function test_ytp_render_shortcode_container_div_theme_default()
  {
    $text = ytp_render_shortcode_container_div('ytp-test', array());
    $this->assertStringContainsString("<div class='ytp-test ytp-default'>", $text);
  }

  /**
   * @covers ::ytp_render_shortcode_container_div
   */
  function test_ytp_render_shortcode_container_div_theme_light()
  {
    $text = ytp_render_shortcode_container_div('ytp-test', array('theme' => 'light'));
    $this->assertStringContainsString("<div class='ytp-test ytp-light'>", $text);
  }

  /**
   * @covers ::ytp_render_shortcode_container_div
   */
  function test_ytp_render_shortcode_container_div_theme_dark()
  {
    $text = ytp_render_shortcode_container_div('ytp-test-two', array('theme' => 'dark'));
    $this->assertStringContainsString("<div class='ytp-test-two ytp-dark'>", $text);
  }

  /**
   * @covers ::ytp_render_shortcode_container_div
   */
  function test_ytp_render_shortcode_container_div_theme_custom()
  {
    $text = ytp_render_shortcode_container_div('ytp-test', array('theme' => 'whatever'));
    $this->assertStringContainsString("<div class='ytp-test whatever'>", $text);
  }

  /**
   * @covers ::ytp_log
   */
  function test_ytp_log_string_expecting_string()
  {
    \LogCapture::start();
    \ytp_log("my log content");
    $result = \LogCapture::end_get();
    $this->assertStringContainsString("YESTICKET: my log content", $result);
  }

  /**
   * @covers ::ytp_log
   */
  function test_ytp_log_array_expecting_serialized_string()
  {
    \LogCapture::start();
    \ytp_log(array("my-key" => "my-value"));
    $result = \LogCapture::end_get();
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
    \add_filter('locale', function () {
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

  /**
   * @param string $timezone a valid timezone descriptor. If $gmt_offset is used, this will be the expected value.
   * @param string $datetime_string in format 'Y-m-d H:i:s'
   * @param string $gmt_offset in format '+-HH:MM'
   */
  function run_ytp_to_local_datetime($timezone, $datetime_string, $gmt_offset = NULL)
  {
    delete_option('timezone_string');
    delete_option('gmt_offset');
    if (isset($gmt_offset)) {
      $this->assertTrue(\add_option('gmt_offset', $gmt_offset), "Should have changed timezone");
    } else {
      $this->assertTrue(\add_option('timezone_string', $timezone), "Should have changed timezone");
    }
    $this->assertSame($timezone, wp_timezone()->getName());
    $result = ytp_to_local_datetime($datetime_string);
    $this->assertSame($timezone, $result->getTimezone()->getName(), "Timezone should be '$timezone'");
    $this->assertSame($datetime_string, \wp_date('Y-m-d H:i:s', $result->getTimestamp()));
  }

  /**
   * @covers ::ytp_to_local_datetime
   */
  function test_ytp_to_local_datetime()
  {
    $this->run_ytp_to_local_datetime('UTC', '2022-03-27 20:00:00');
    $this->run_ytp_to_local_datetime('Europe/Berlin', '2022-03-27 20:00:00');
    $this->run_ytp_to_local_datetime('+01:00', '2022-03-27 20:00:00', '1');
    $this->run_ytp_to_local_datetime('-01:00', '2022-03-27 20:00:00', '-1');
    $this->run_ytp_to_local_datetime('+01:15', '2022-03-27 20:00:00', '1.25');
  }

  /**
   * @covers ::ytp_render_date_and_time
   */
  function test_ytp_render_date_and_time()
  {
    $translated = false;
    assertTranslate($translated, 'F j, Y \a\\t g:i A');
    ob_start();
    ytp_render_date_and_time('2022-02-01 20:00:00');
    $this->assertSame('February 1, 2022 at 8:00 PM', ob_get_clean());
    $this->assertTrue($translated, "Should have called translate");
  }

  /**
   * @covers ::ytp_render_date
   */
  function test_ytp_render_date()
  {
    $translated = false;
    assertTranslate($translated, 'F j, Y');
    $this->assertSame('February 1, 2022', ytp_render_date('2022-02-01 20:00:00'));
    $this->assertTrue($translated, "Should have called translate");
  }

  /**
   * @covers ::ytp_render_time
   */
  function test_ytp_render_time()
  {
    $translated = false;
    assertTranslate($translated, 'g:i A');
    $this->assertSame('8:00 PM', ytp_render_time('2022-02-01 20:00:00'));
    $this->assertTrue($translated, "Should have called translate");
  }
}
