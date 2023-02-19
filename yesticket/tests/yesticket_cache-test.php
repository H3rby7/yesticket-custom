<?php

class YesTicketCacheTest extends \WP_Mock\Tools\TestCase
{

  function test_class_exists()
  {
    $this->assertTrue(class_exists("YesTicketCache"));
  }
  function test_get_instance()
  {
    $this->assertNotEmpty(YesTicketCache::getInstance());
  }
  function test_clear()
  {
    \WP_Mock::setUp();
    \WP_Mock::userFunction( 'get_option', array(
      'times' => 1,
      'args' => array('*'),
      'return' => array(),
    ) );
    get_option('aaa');
    YesTicketCache::getInstance()->clear();
    \WP_Mock::tearDown();
  }

}
