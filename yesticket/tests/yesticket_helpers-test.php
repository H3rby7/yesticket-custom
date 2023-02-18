<?php

class YesTicketHelpersTest extends WP_UnitTestCase
{
  function test_ytp_getImageUrl()
  {
    $this->assertSame('http://example.org/wp-content/plugins/app/img/', ytp_getImageUrl(''));
    $this->assertSame('http://example.org/wp-content/plugins/app/img/my-image.png', ytp_getImageUrl('my-image.png'));
  }

  function test_is_countable()
  {
    $this->assertTrue(is_countable(array()));
    $this->assertTrue(is_countable(array("a" => "b")));
    $this->assertFalse(is_countable(NULL));
  }
}
