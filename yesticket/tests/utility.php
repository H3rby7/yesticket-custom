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
function getCachedImage($type, $renderer, $qualityArg = null) {
  $image = \imagecreatetruecolor(10, 10);
  \ob_start();
  Assert::assertTrue($renderer($image, null, $qualityArg), 'should be able to create image');
  return new CachedImage($type, \ob_get_clean());
}