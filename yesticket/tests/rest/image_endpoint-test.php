<?php

namespace YesTicket;

use \YesTicket\ImageCache;
use \YesTicket\Rest\ImageEndpoint;

// As seen in https://torquemag.io/2017/01/testing-api-endpoints/
class ImageEndpointTest extends \WP_UnitTestCase
{
  protected function setUp(): void
  {
    parent::setUp();
    /** @var WP_REST_Server $wp_rest_server */
    global $wp_rest_server;
    $this->server = $wp_rest_server = new \WP_REST_Server;
    do_action('rest_api_init');
  }

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Rest\ImageEndpoint"));
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  public function test_register_route()
  {
    $routes = $this->server->get_routes('yesticket/v1');
    $this->assertArrayHasKey('/yesticket/v1/picture/(?P<event_id>\d+)', $routes);
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  public function test_endpoints()
  {
    $routes = $this->server->get_routes('yesticket/v1');
    foreach ($routes as $route => $route_config) {
      if (0 === strpos('/yesticket/v1/picture/(?P<event_id>\d+)', $route)) {
        $this->assertTrue(is_array($route_config));
        foreach ($route_config as $i => $endpoint) {
          $this->assertArrayHasKey('callback', $endpoint);
          $this->assertCount(2, $endpoint['callback'], 'Callback should have two items');
          $this->assertTrue(is_callable(array($endpoint['callback'][0], $endpoint['callback'][1])), "Callback should be valid and callable!");
        }
      }
    }
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_get_instance()
  {
    $_class = new \ReflectionClass(ImageEndpoint::class);
    $_instance_prop = $_class->getProperty("instance");
    $_instance_prop->setAccessible(true);
    $_instance_prop->setValue(NULL);
    $this->assertNotEmpty(ImageEndpoint::getInstance());
    $_instance_prop->setAccessible(false);
  }

  /**
   * Initiate Mock for @see ImageCache
   * 
   * @param string $expected_url
   * @param mixed $mock_result
   */
  private function initMock()
  {
    // Inject Mock into API::$instance
    $_cache_property = new \ReflectionProperty(ImageEndpoint::class, "cache");
    $_cache_property->setAccessible(true);
    $instance = ImageEndpoint::getInstance();
    $cache_mock = $this->getMockBuilder(ImageCache::class)
      ->setMethods(['getFromCacheOrFresh'])
      ->getMock();
    $_cache_property->setValue($instance, $cache_mock);
    return $cache_mock;
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_handleRequest_200()
  {
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    \delete_transient(Cache::getInstance()->cacheKey($get_url));
    $mock_result = \imagecreate(10, 10);
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->will($this->returnValue($mock_result));
    $request = new \WP_REST_Request('GET', '/yesticket/v1/picture/123');
    \ob_start();
    $response = @$this->server->dispatch($request);
    $image = \ob_get_clean();
    $this->assertSame(200, $response->get_status());
    $this->assertStringContainsString('quality = 100', $image);
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_handleRequest_cache_throws()
  {
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    \delete_transient(Cache::getInstance()->cacheKey($get_url));
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->willThrowException(new \RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", "yesticket")));
    $request = new \WP_REST_Request('GET', '/yesticket/v1/picture/123');
    $response = @$this->server->dispatch($request);
    $this->assertTrue($response->get_status() > 299, "Should not be OK");
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_handleRequest_cache_returns_false()
  {
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    \delete_transient(Cache::getInstance()->cacheKey($get_url));
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->will($this->returnValue(FALSE));
    $request = new \WP_REST_Request('GET', '/yesticket/v1/picture/123');
    $response = @$this->server->dispatch($request);
    $this->assertTrue($response->get_status() > 299, "Should not be OK");
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint::validationCallback
   */
  function test_validations()
  {
    $this->assertFalse(ImageEndpoint::getInstance()->validationCallback("a"), "String should not be valid!");
    $this->assertFalse(ImageEndpoint::getInstance()->validationCallback("0"), "'0' should not be valid!");
    $this->assertFalse(ImageEndpoint::getInstance()->validationCallback("1.5"), "'1.5' should not be valid!");
    $this->assertTrue(ImageEndpoint::getInstance()->validationCallback("1"), "'1' should be valid!");
  }
}
