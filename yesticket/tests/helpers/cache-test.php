<?php

namespace YesTicket;

use LogCapture;
use \YesTicket\Cache;

include_once(__DIR__ . "/../utility.php");

class TestCacheImpl extends Cache
{
  public $addedKey = false;
  static public function getInstance()
  {
  }
  public function call_ensureOptionExists()
  {
    parent::ensureOptionExists();
  }
  public function call_addKeyToActiveCaches($cacheKey)
  {
    parent::addKeyToActiveCaches($cacheKey);
  }
  protected function addKeyToActiveCaches($cacheKey)
  {
    $this->addedKey = $cacheKey;
  }
  public function call_cache($cacheKey, $data)
  {
    parent::cache($cacheKey, $data);
  }
  public function call_logRequestMasked($url)
  {
    parent::logRequestMasked($url);
  }
}

class CacheTest extends \WP_UnitTestCase
{
  private $opt_key = 'yesticket_transient_keys';
  private $testClass;

  function setUp(): void
  {
    $this->testClass = new TestCacheImpl();
  }

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Cache"));
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_ensureOptionExists_option_absent()
  {
    \delete_option($this->opt_key);
    $this->assertFalse(\get_option($this->opt_key, false), "Test should start with option being absent.");
    $this->testClass->call_ensureOptionExists();
    $option = \get_option($this->opt_key, false);
    $this->assertIsArray($option);
    $this->assertCount(0, $option);
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_ensureOptionExists_option_not_an_array()
  {
    \update_option($this->opt_key, "a-string instead of an array! Scandalous!");
    $this->testClass->call_ensureOptionExists();
    $option = \get_option($this->opt_key, false);
    $this->assertIsArray($option);
    $this->assertCount(0, $option);
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_ensureOptionExists_option_already_present()
  {
    \update_option($this->opt_key, ["some-key", "another-key"]);
    $this->testClass->call_ensureOptionExists();
    $option = \get_option($this->opt_key, false);
    $this->assertNotEmpty($option);
    $this->assertIsArray($option);
    $this->assertSameSets(["some-key", "another-key"], $option, "Should contain the previously set values.");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_cacheKey()
  {
    $very_long_url = 'https://yesticket.org/some/very/long/url/with/more/than/172/characters/to/verify/the/method/keeps/its/limit/omg/that/requires/a/lot/of/text/I/did/not/think/that/far/but/now/I/got/it';
    $cacheKey = $this->testClass->cacheKey($very_long_url);
    $this->assertNotEmpty($cacheKey);
    $this->assertTrue(strlen($cacheKey) < 172, "Transient key must be <172 characters!");
    $this->assertSame($cacheKey, $this->testClass->cacheKey($very_long_url), "Should be deterministic.");
    $this->assertFalse(
      $this->testClass->cacheKey('a') == $this->testClass->cacheKey('b'),
      "Should return different results for different inputs"
    );
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_addKeyToActiveCaches_empty_arr()
  {
    \update_option($this->opt_key, []);
    $this->testClass->call_addKeyToActiveCaches('my-test-key');
    $option = \get_option($this->opt_key, false);
    $this->assertNotEmpty($option);
    $this->assertIsArray($option);
    $this->assertSameSets(['my-test-key'], $option, "Should contain the new value.");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_addKeyToActiveCaches_arr_already_has_it()
  {
    \update_option($this->opt_key, ['my-test-key']);
    $this->testClass->call_addKeyToActiveCaches('my-test-key');
    $option = \get_option($this->opt_key, false);
    $this->assertNotEmpty($option);
    $this->assertIsArray($option);
    $this->assertSameSets(['my-test-key'], $option, "Should contain the new value (and only once)");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_addKeyToActiveCaches_arr_has_another_item()
  {
    \update_option($this->opt_key, ['my-test-key']);
    $this->testClass->call_addKeyToActiveCaches('my-other-key');
    $option = \get_option($this->opt_key, false);
    $this->assertNotEmpty($option);
    $this->assertIsArray($option);
    $this->assertSameSets(['my-test-key', 'my-other-key'], $option, "Should contain both values");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_cache_storing_works()
  {
    $key = 'test-A';
    \delete_transient($key);
    $this->assertEmpty(\get_transient($key), "Test should start with transient being absent.");
    $this->testClass->call_cache($key, 'value-for-a');
    $transient = \get_transient($key);
    $this->assertNotEmpty($transient);
    $this->assertSame('value-for-a', $transient, "Transient should contain our data.");
    $this->assertSame($key, $this->testClass->addedKey, "Should call 'addKeyToActiveCaches' with param.");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_option_empty()
  {
    // Empty option, expect no error
    \update_option($this->opt_key, []);
    Cache::clear();
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_one_transient_given()
  {
    // given transient 'test-A', enlisted in the option
    \set_transient('test-A', 'value-A', 0);
    \update_option($this->opt_key,  ['test-A']);
    // clear cache
    Cache::clear();
    // expect option [] and transient gone (FALSE)
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));
    $this->assertFalse(\get_transient('test-A'));
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_two_transients_given_only_one_to_be_cleared()
  {
    // given transient 'test-A', enlisted in the option
    // and an unrelated transient
    \set_transient('test-A', 'value-A', 0);
    \set_transient('unrelated-B', 'value-B', 0);
    \update_option($this->opt_key,  ['test-A']);
    // clear cache
    Cache::clear();
    // expect option [] and transient 'test-A' gone (FALSE)
    // and unrelated transient to live on.
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));
    $this->assertFalse(\get_transient('test-A'));
    $this->assertNotEmpty(\get_transient('unrelated-B'));
    // clean up
    \delete_transient('unrelated-B');
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_two_transients_given_noth_to_be_cleared()
  {
    // given two transients, both enlisted in the option
    \set_transient('test-A', 'value-A', 0);
    \set_transient('test-B', 'value-B', 0);
    \update_option($this->opt_key,  ['test-A', 'test-B']);
    // clear cache
    Cache::clear();
    // expect option [] and transients gone (FALSE)
    $this->assertIsArray(\get_option($this->opt_key));
    $this->assertCount(0, \get_option($this->opt_key));
    $this->assertFalse(\get_transient('test-A'));
    $this->assertFalse(\get_transient('test-B'));
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_logRequestMasked_opaque()
  {
    LogCapture::start();
    $this->testClass->call_logRequestMasked('https://my.awesome.url/aaa');
    $logged = LogCapture::end_get();
    $this->assertStringContainsString('No cache present', $logged);
    $this->assertStringContainsString('https://my.awesome.url/aaa', $logged);
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_logRequestMasked_with_secrets_expect_no_confidentials()
  {
    LogCapture::start();
    $this->testClass->call_logRequestMasked('https://my.awesome.url/aaa?organizer=821&key=myawesomeapikey');
    $logged = LogCapture::end_get();
    $this->assertStringContainsString('No cache present', $logged);
    $this->assertStringContainsString('https://my.awesome.url/aaa', $logged);
    $this->assertStringContainsString('organizer=', $logged);
    $this->assertStringContainsString('key=', $logged);
    $this->assertStringNotContainsString('organizer=821', $logged);
    $this->assertStringNotContainsString('key=myawesomeapikey', $logged);
  }
}
