<?php

/**
 * Class Test_Sample
 *
 * @package YesTicket
 */

class SampleTest extends WP_UnitTestCase
{
  function test_ytp_getImageUrl()
  {
    $this->assertSame('http://example.org/wp-content/plugins/app/img/', ytp_getImageUrl(''));
    $this->assertSame('http://example.org/wp-content/plugins/app/img/my-image.png', ytp_getImageUrl('my-image.png'));
  }
}
