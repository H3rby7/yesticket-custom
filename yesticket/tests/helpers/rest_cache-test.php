<?php

namespace YesTicket;
use WP_Http;

class RestCacheTest extends \WP_UnitTestCase
{
  private $opt_key = 'yesticket_transient_keys';

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\RestCache"));
  }

  /**
   * @covers YesTicket\RestCache
   */
  function test_get_instance()
  {
    $_class = new \ReflectionClass(RestCache::class);
    $_instance_prop = $_class->getProperty("instance");
    $_instance_prop->setAccessible(true);
    $_instance_prop->setValue(NULL);
    $this->assertNotEmpty(RestCache::getInstance());
    $opt = \get_option($this->opt_key);
    $this->assertIsArray($opt);
    $this->assertCount(0, $opt);
    $_instance_prop->setAccessible(false);
  }

  /**
   * @covers YesTicket\RestCache
   */
  function test_cacheKey()
  {
    $very_long_url = 'https://yesticket.org/some/very/long/url/with/more/than/172/characters/to/verify/the/method/keeps/its/limit/omg/that/requires/a/lot/of/text/I/did/not/think/that/far/but/now/I/got/it';
    $cacheKey = RestCache::getInstance()->cacheKey($very_long_url);
    $this->assertNotEmpty($cacheKey);
    $this->assertTrue(strlen($cacheKey) < 172, "Transient key must be <172 characters!");
    $this->assertSame($cacheKey, RestCache::getInstance()->cacheKey($very_long_url), "Should be deterministic.");
    $this->assertFalse(
      RestCache::getInstance()->cacheKey('a') == RestCache::getInstance()->cacheKey('b'),
      "Should return different results for different inputs"
    );
  }

  /**
   * @covers YesTicket\RestCache
   */
  function test_clear()
  {
    // Empty option, expect no error
    RestCache::getInstance()->clear();
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));

    // given transient 'test-A', enlisted in the option
    \set_transient('test-A', 'value-A', 0);
    \update_option($this->opt_key,  ['test-A']);
    // clear cache
    RestCache::getInstance()->clear();
    // expect option [] and transient gone (FALSE)
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));
    $this->assertFalse(get_transient('test-A'));

    // given transient 'test-A', enlisted in the option
    // and an unrelated transient
    \set_transient('test-A', 'value-A', 0);
    \set_transient('unrelated-B', 'value-B', 0);
    \update_option($this->opt_key,  ['test-A']);
    // clear cache
    RestCache::getInstance()->clear();
    // expect option [] and transient 'test-A' gone (FALSE)
    // and unrelated transient to live on.
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));
    $this->assertFalse(get_transient('test-A'));
    $this->assertNotEmpty(get_transient('unrelated-B'));
    // clean up
    \delete_transient('unrelated-B');

    // given two transients, both enlisted in the option
    \set_transient('test-A', 'value-A', 0);
    \set_transient('test-B', 'value-B', 0);
    \update_option($this->opt_key,  ['test-A', 'test-B']);
    // clear cache
    RestCache::getInstance()->clear();
    // expect option [] and transients gone (FALSE)
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));
    $this->assertFalse(get_transient('test-A'));
    $this->assertFalse(get_transient('test-B'));
  }

  /**
   * @covers YesTicket\RestCache
   */
  function test_getFromCacheOrFresh()
  {
    // constants
    $get_url = 'test-url';
    $cacheKey = RestCache::getInstance()->cacheKey($get_url);

    // General Setup
    $pre_http_request_filter_has_run = false;
    $external_call_url = '';
    // Setup MOCK for HTTP call
    remove_all_filters('pre_http_request');
    \add_filter('pre_http_request', function ($preempt, $parsed_args, $url) use (&$pre_http_request_filter_has_run, &$external_call_url) {
      $pre_http_request_filter_has_run = true;
      $external_call_url = $url;
      return array(
        'headers'     => array(),
        'cookies'     => array(),
        'filename'    => null,
        'response'    => array('code' => WP_Http::OK, 'message' => 'OK'),
        'status_code' => WP_Http::OK,
        'success'     => 1,
        'body'        => '{"a-key": "a-value"}',
      );
    }, 10, 3);

    // Given no cached item
    $pre_http_request_filter_has_run = false;
    $external_call_url = '';
    \delete_transient($cacheKey);
    // Call
    $result = RestCache::getInstance()->getFromCacheOrFresh($get_url);
    // Check Mock was invoked
    $this->assertTrue($pre_http_request_filter_has_run, "Should make HTTP call.");
    $this->assertSame($external_call_url, $get_url, "Called wrong url");
    // Check response from Mock was used
    $this->assertNotEmpty($result);
    $this->assertSame('{"a-key": "a-value"}', $result, "Expect result to match mocked response");
    // Check new cache item equals Mock response
    $cache = get_transient($cacheKey);
    $this->assertNotEmpty($cache);
    $this->assertSame('{"a-key": "a-value"}', $cache, "Expect new cache to match mocked response");

    // Given cached item is present (from previous test)
    $pre_http_request_filter_has_run = false;
    $external_call_url = '';
    // Call
    $result = RestCache::getInstance()->getFromCacheOrFresh($get_url);
    $this->assertFalse($pre_http_request_filter_has_run, "Should have used the cache.");
    $this->assertNotEmpty($result);
    $this->assertSame('{"a-key": "a-value"}', $result, "Expect result to match mocked response");
  }

  /**
   * @covers YesTicket\RestCache
   */
  function test_gettingErrorFromApi()
  {
    $get_url = 'test-url';
    \delete_transient(RestCache::getInstance()->cacheKey($get_url));

    // Setup MOCK for HTTP call
    remove_all_filters('pre_http_request');
    \add_filter('pre_http_request', function ($preempt, $parsed_args, $url) {
      return array(
        'headers'     => array(),
        'cookies'     => array(),
        'filename'    => null,
        'response'    => array('code' => WP_Http::SERVICE_UNAVAILABLE, 'message' => 'API down for maintainance'),
        'status_code' => WP_Http::SERVICE_UNAVAILABLE,
        'success'     => 0,
        'body'        => '',
      );
    }, 10, 3);
    $this->expectException(\RuntimeException::class);
    RestCache::getInstance()->getFromCacheOrFresh($get_url);
  }
}
