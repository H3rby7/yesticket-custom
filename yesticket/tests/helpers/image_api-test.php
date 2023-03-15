<?php


use \YesTicket\ImageApi;
use \YesTicket\ImageCache;
use YesTicket\Model\CachedImage;

// As seen in https://torquemag.io/2017/01/testing-api-endpoints/
class ImageApiTest extends \WP_UnitTestCase
{

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\ImageApi"));
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_get_instance()
  {
    $_class = new ReflectionClass(ImageApi::class);
    $_instance_prop = $_class->getProperty("instance");
    $_instance_prop->setAccessible(true);
    $_instance_prop->setValue(NULL);
    $this->assertNotEmpty(ImageApi::getInstance());
    $_instance_prop->setAccessible(false);
  }

  /**
   * Initiate Mock for @see ImageCache
   */
  private function initMock()
  {
    // Inject Mock into ImageApi::$instance
    $_cache_property = new ReflectionProperty(ImageApi::class, "cache");
    $_cache_property->setAccessible(true);
    $instance = ImageApi::getInstance();
    $cache_mock = $this->getMockBuilder(ImageCache::class)
      ->setMethods(['getFromCacheOrFresh'])
      ->getMock();
    $_cache_property->setValue($instance, $cache_mock);
    return $cache_mock;
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_returns_cachedImage()
  {
    // Define our http-get endpoint
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    // Set up mock to return a png on 'image/png'
    $mock_result = new CachedImage();
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with(
        $get_url,
        $this->callback(function ($getFunction) {
          return \is_callable($getFunction);
        })
      )
      ->will($this->returnValue($mock_result));
    // Call and assert
    $this->assertSame($mock_result, ImageApi::getInstance()->getEventImage(123));
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_returns_wp_error()
  {
    // Define our http-get endpoint
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    // Set up mock to throw an ImageException
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once(0))
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->will($this->returnValue(new WP_Error(503)));
    $result = ImageApi::getInstance()->getEventImage(123);
    $this->assertTrue(\is_wp_error($result));
    $this->assertSame($get_url, $result->get_error_data(), "Error data should be the URL to the actual yesticket resource.");
  }

  

  /**
   * @covers YesTicket\ImageApi
   */
  function test_get_function()
  {
    // Define our http-get endpoint
    $event_id = 1;
    $expected_url = "https://www.yesticket.org/dev/picture.php?event=$event_id";
    // Set up mock to return a png on 'image/png'
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with(
        $expected_url,
        $this->callback(function ($getFunction) use ($expected_url) {
          $this->assertLogsAndReturnsWpError($getFunction, $expected_url);
          return true;
        })
      )
      ->will($this->returnValue(new CachedImage()));
    ImageApi::getInstance()->getEventImage($event_id);
  }

  
  /**
   * Given 'HEAD' Request returns WP_Error
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertLogsAndReturnsWpError($f, $expected_url)
  {
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
    // Call
    $result = $f($expected_url);
    // Check Mock was invoked
    $this->assertTrue($pre_http_request_filter_has_run, "Should make HTTP call.");
    $this->assertSame($expected_url, $external_call_url, "Called wrong url");
  }

}
