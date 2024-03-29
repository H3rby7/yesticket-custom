<?php

include_once(__DIR__ . "/../utility.php");

class HelperFunctionsTest extends WP_UnitTestCase
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
   * @covers ::ytp_info
   */
  function test_ytp_info_string_expecting_string()
  {
    LogCapture::start();
    \ytp_info("/path/to/plugins/yesticket/src/file.php", 14, "my log content");
    $result = LogCapture::end_get();
    $this->assertStringContainsString("[YESTICKET]/file.php@14: my log content", $result);
  }

  /**
   * @covers ::ytp_info
   */
  function test_ytp_info_array_expecting_serialized_string()
  {
    LogCapture::start();
    \ytp_info("/path/to/plugins/yesticket/src/other/file.php", 69, array("my-key" => "my-value"));
    $result = LogCapture::end_get();
    $this->assertStringContainsString('[YESTICKET]/other/file.php@69: Array', $result);
    $this->assertStringContainsString('[my-key] => my-value', $result);
  }

  /**
   * @covers ::ytp_debug
   */
  function test_ytp_debug_string_expecting_string()
  {
    LogCapture::start();
    \ytp_debug(".../yesticket/src/file.php", 161, "less important message");
    $result = LogCapture::end_get();
    if (true === WP_DEBUG) {
      $this->assertStringContainsString("[YESTICKET]/file.php@161: less important message", $result);
    } else {
      $this->assertEmpty($result, "debugging was not enabled, should not log.");
    }
  }

  /**
   * @covers ::ytp_debug
   */
  function test_ytp_debug_array_expecting_serialized_string()
  {
    LogCapture::start();
    \ytp_debug(".../yesticket/src/file.php", 161, array("is-this-important?" => "less so."));
    $result = LogCapture::end_get();
    if (true === WP_DEBUG) {
      $this->assertStringContainsString('[YESTICKET]/file.php@161: Array', $result);
      $this->assertStringContainsString('[is-this-important?] => less so.', $result);
    } else {
      $this->assertEmpty($result, "debugging was not enabled, should not log.");
    }
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

  /**
   * @covers ::strpos_findLast_viaRegex
   */
  function test_strpos_findLast_viaRegex()
  {
    // Look for 'n'; given it only occurs once.
    $this->assertSame(
      15,
      \strpos_findLast_viaRegex("this is my string", "/n/i"),
      "Letter 'n' is at position 15!"
    );
    // Look for 't'; occuring two times
    $this->assertSame(
      12,
      \strpos_findLast_viaRegex("this is my string", "/t/i"),
      "Letter 't' is at position 12!"
    );
    // Look for r or y '[ry]'; 
    $this->assertSame(
      13,
      \strpos_findLast_viaRegex("this is my string", "/[ry]/i"),
      "Letter 'r' is last and at position 13!"
    );
    // Look for 'x'; expecting FALSE
    $this->assertFalse(
      \strpos_findLast_viaRegex("this is my string", "/x/i"),
      "Letter 'x' is not present, Should return FALSE"
    );
  }
}
