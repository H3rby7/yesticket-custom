<?php

function assertTranslate(&$wasCalled, $expectedInput)
{
  $wasCalled = false;
  remove_all_filters('gettext', 69);
  add_filter('gettext', function ($translated_text, $untranslated_text, $domain) use (&$wasCalled, $expectedInput) {
    $wasCalled = true;
    PHPUnit\Framework\Assert::assertSame($domain, 'yesticket');
    PHPUnit\Framework\Assert::assertSame($expectedInput, $untranslated_text);
    return $translated_text;
  }, 69, 3);
}
