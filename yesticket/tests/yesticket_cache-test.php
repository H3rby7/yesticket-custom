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

  function test_getFromCacheOrFresh()
  {
    $pre_http_request_filter_has_run = false;
    $external_call_url = '';

    // No cached items present
    update_option($this->opt_key, array());
    add_filter('pre_http_request', function ($preempt, $parsed_args, $url) use ( &$pre_http_request_filter_has_run, &$external_call_url ) {
      echo 'mocked response used';
      $pre_http_request_filter_has_run = true;
      $external_call_url = $url;
      return array(
        'headers'     => array(),
        'cookies'     => array(),
        'filename'    => null,
        'response'    => array('code' => WP_Http::OK, 'message' => 'OK'),
        'status_code' => 200,
        'success'     => 1,
        'body'        => '{"a-key": "a-value"}',
      );
    }, 10, 3);

    // Call function
    $result = YesTicketCache::getInstance()->getFromCacheOrFresh('test-url');
    $this->assertTrue($pre_http_request_filter_has_run, "Should make HTTP call.");
    $this->assertSame($external_call_url, "test-url", "Called wrong url");
    $this->assertNotEmpty($result);
    $this->assertNotEmpty($result->{'a-key'}, "Expect result to match mocked response");
    $this->assertSame('a-value', $result->{'a-key'}, "Expect result to match mocked response");
  }
}
