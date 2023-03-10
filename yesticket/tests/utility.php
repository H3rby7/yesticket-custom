<?php

use \PHPUnit\Framework\Assert;
use \YesTicket\Model\CachedImage;

function assertTranslate(&$wasCalled, $expectedInput)
{
  $wasCalled = false;
  \remove_all_filters('gettext', 69);
  \add_filter('gettext', function ($translated_text, $untranslated_text, $domain) use (&$wasCalled, $expectedInput) {
    $wasCalled = true;
    Assert::assertSame($domain, 'yesticket');
    Assert::assertSame($expectedInput, $untranslated_text);
    return $translated_text;
  }, 69, 3);
}


/**
 * @param callable $renderer \imagexxxx call
 * @param int $qualityArg 3rd param (quality) used in \imagexxxx call
 * @return CachedImage image
 */
function getCachedImage($type, $renderer, $qualityArg = 0)
{
  $image = \imagecreatetruecolor(10, 10);
  \ob_start();
  Assert::assertTrue($renderer($image, null, $qualityArg), 'should be able to create image');
  return new CachedImage($type, \ob_get_clean());
}

class LogCapture
{
  static private $instance;

  static private function getInstance()
  {
      if (!isset(LogCapture::$instance)) {
        LogCapture::$instance = new LogCapture();
      }
      return LogCapture::$instance;
  }

  private $tmpFile;
  private $oldLocation;
  private $open = false;

  /**
   * Redirect error_log to our capturing
   */
  static public function start() {
    $lc = LogCapture::getInstance();
    Assert::assertFalse($lc->open, ">>> LogCapture -> Capturing already ongoing!");
    $lc->open = true;
    $lc->tmpFile = tmpfile();
    $lc->oldLocation = ini_set('error_log', stream_get_meta_data($lc->tmpFile)['uri']);
  }
  /**
   * Restore error_log to normal state and return captured content
   * @return string content
   */
  static public function end_get() {
    $lc = LogCapture::getInstance();
    Assert::assertTrue($lc->open, ">>> LogCapture -> Capturing was not running!");
    $result = stream_get_contents($lc->tmpFile);
    ini_set('error_log', $lc->oldLocation);
    $lc->open = false;
    return $result;
  }
  function __destruct()
  {
    if ($this->open) {
      LogCapture::end_get();
      throw new AssertionError('>>> LogCapture -> NOT CLOSED! YOUR LOGGING MAY BE INCONSISTENT. Please verify you called ::end_get for every ::start');
    }
  }
}
