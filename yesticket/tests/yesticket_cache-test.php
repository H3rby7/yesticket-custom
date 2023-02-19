<?php

class YesTicketCacheTest extends WP_UnitTestCase
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
    YesTicketCache::getInstance()->clear();
  }

}
