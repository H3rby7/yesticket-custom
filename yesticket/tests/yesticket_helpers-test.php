<?php

class YesTicketHelpersTest extends WP_UnitTestCase
{
  /**
   * @covers ::ytp_getImageUrl
   */
  function test_ytp_getImageUrl()
  {
    $this->assertSame('http://example.org/wp-content/plugins/app/yesticket/img/', ytp_getImageUrl(''));
    $this->assertSame('http://example.org/wp-content/plugins/app/yesticket/img/my-image.png', ytp_getImageUrl('my-image.png'));
  }

  /**
   * @covers ::is_countable
   */
  function test_is_countable()
  {
    $this->assertTrue(is_countable(array()));
    $this->assertTrue(is_countable(array("a" => "b")));
    $this->assertFalse(is_countable(NULL));
  }
}
