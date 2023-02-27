<?php

use \PHPUnit\Framework\Assert;

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
