<?php

use \YesTicket\Cache;

include_once(__DIR__ . "/../utility.php");

class TestCacheImpl extends Cache
{
  static public function getInstance()
  {
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

class CacheTest extends WP_UnitTestCase
{
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
  function test_cache_storing_works()
  {
    $key = 'test-A';
    \delete_transient($key);
    $this->assertEmpty(\get_transient($key), "Test should start with transient being absent.");
    $this->testClass->call_cache($key, 'value-for-a');
    $transient = \get_transient($key);
    $this->assertNotEmpty($transient);
    $this->assertSame('value-for-a', $transient, "Transient should contain our data.");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_one_transient_given()
  {
    $cacheKey = $this->testClass->cacheKey('test-A');
    \set_transient($cacheKey, 'value-A', 0);
    // Check we could set transient
    $this->assertNotEmpty(\get_transient($cacheKey), "Transient should have been available.");
    // clear cache
    global $wpdb;
    $this->assertTrue(Cache::clear($wpdb));
    $this->assertFalse(\get_transient($cacheKey), "Transient should have been cleared.");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_two_transients_given_only_one_to_be_cleared()
  {
    $cacheKey = $this->testClass->cacheKey('test-A');
    \set_transient($cacheKey, 'value-A', 0);
    \set_transient('unrelated-B', 'value-B', 0);
    // Check we could set transients
    $this->assertNotEmpty(\get_transient($cacheKey), "Transient should have been available.");
    $this->assertNotEmpty(\get_transient('unrelated-B'), "Transient should have been available.");
    // clear cache
    global $wpdb;
    $this->assertTrue(Cache::clear($wpdb));
    $this->assertFalse(\get_transient($cacheKey), "Transient should have been cleared.");
    $this->assertNotEmpty(\get_transient('unrelated-B'), "Transient should still be available after ::clear.");
    \delete_transient('unrelated-B');
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_two_transients_given_noth_to_be_cleared()
  {
    $cacheKeyA = $this->testClass->cacheKey('test-A');
    $cacheKeyB = $this->testClass->cacheKey('b-key');
    \set_transient($cacheKeyA, 'value-A', 0);
    \set_transient($cacheKeyB, 'Bs value is this', 0);
    // Check we could set transients
    $this->assertNotEmpty(\get_transient($cacheKeyA), "Transient A should have been available.");
    $this->assertNotEmpty(\get_transient($cacheKeyB), "Transient B should have been available.");
    global $wpdb;
    $this->assertTrue(Cache::clear($wpdb));
    $this->assertFalse(\get_transient($cacheKeyA), "Transient A should have been cleared.");
    $this->assertFalse(\get_transient($cacheKeyB), "Transient B should have been cleared.");
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_db_error()
  {
    // Set-Up DB mock
    $wpdb_mock = $this->getMockBuilder(wpdb::class)
      ->disableOriginalConstructor()
      ->setMethods(['get_results'])
      ->getMock();
    $wpdb_mock->expects($this->once())
      ->method('get_results')
      ->with()
      ->will($this->returnValue(null));
    $wpdb_mock->last_error = 'test last error of wpdb';
    $wpdb_mock->prefix = 'wp_';
    LogCapture::start();
    $this->assertFalse(Cache::clear($wpdb_mock));
    $logged = LogCapture::end_get();
    $this->assertStringContainsString('test last error of wpdb', $logged);
  }

  /**
   * @covers YesTicket\Cache
   */
  function test_clear_db_transient_not_present_anymore()
  {
    \delete_transient('test-A');
    // Set-Up DB mock
    $wpdb_mock = $this->getMockBuilder(wpdb::class)
      ->disableOriginalConstructor()
      ->setMethods(['get_results'])
      ->getMock();
    $wpdb_mock->expects($this->once())
      ->method('get_results')
      ->with()
      ->will($this->returnValue(array(array('test-A'))));
    $wpdb_mock->last_error = null;
    $wpdb_mock->prefix = 'wp_';
    $this->assertFalse(Cache::clear($wpdb_mock));
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
