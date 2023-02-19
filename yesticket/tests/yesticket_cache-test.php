<?php

class YesTicketCacheTest extends WP_UnitTestCase
{
  private $opt_key = 'yesticket_transient_keys';

  function test_class_exists()
  {
    $this->assertTrue(class_exists("YesTicketCache"));
  }
  function test_get_instance()
  {
    $this->assertNotEmpty(YesTicketCache::getInstance());
    $opt = get_option($this->opt_key);
    $this->assertIsArray($opt);
    $this->assertCount(0, $opt);
  }
  function test_clear()
  {
    // Empty option, expect no error
    update_option($this->opt_key, array());
    YesTicketCache::getInstance()->clear();
    $this->assertIsArray(get_option($this->opt_key));
    $this->assertCount(0, get_option($this->opt_key));

    // given transient 'test-A', enlisted in the option
    set_transient('test-A', 'value-A', 0);
    update_option($this->opt_key,  ['test-A']);
    // clear cache
    YesTicketCache::getInstance()->clear();
    // expect option [] and transient gone (FALSE)
    $this->assertIsArray(get_option($this->opt_key));
    $this->assertCount(0, get_option($this->opt_key));
    $this->assertFalse(get_transient('test-A'));

    // given transient 'test-A', enlisted in the option
    // and an unrelated transient
    set_transient('test-A', 'value-A', 0);
    set_transient('unrelated-B', 'value-B', 0);
    update_option($this->opt_key,  ['test-A']);
    // clear cache
    YesTicketCache::getInstance()->clear();
    // expect option [] and transient 'test-A' gone (FALSE)
    // and unrelated transient to live on.
    $this->assertIsArray(get_option($this->opt_key));
    $this->assertCount(0, get_option($this->opt_key));
    $this->assertFalse(get_transient('test-A'));
    $this->assertNotEmpty(get_transient('unrelated-B'));
    // clean up
    delete_transient('unrelated-B');

    // given two transients, both enlisted in the option
    set_transient('test-A', 'value-A', 0);
    set_transient('test-B', 'value-B', 0);
    update_option($this->opt_key,  ['test-A', 'test-B']);
    // clear cache
    YesTicketCache::getInstance()->clear();
    // expect option [] and transients gone (FALSE)
    $this->assertIsArray(get_option($this->opt_key));
    $this->assertCount(0, get_option($this->opt_key));
    $this->assertFalse(get_transient('test-A'));
    $this->assertFalse(get_transient('test-B'));
  }
}
