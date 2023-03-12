<?php

namespace YesTicket;

use \YesTicket\ImageCache;
use YesTicket\Model\CachedImage;
use \YesTicket\Rest\ImageEndpoint;
use \WP_REST_Server;

// As seen in https://torquemag.io/2017/01/testing-api-endpoints/
class ImageEndpointTest extends \WP_UnitTestCase
{
  /**
   * @var WP_REST_Server
   */
  protected $server;
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
   * Initiate Mock for @see ImageApi
   */
  private function initMock()
  {
    // Inject Mock into API::$instance
    $_cache_property = new \ReflectionProperty(ImageEndpoint::class, "api");
    $_cache_property->setAccessible(true);
    $instance = ImageEndpoint::getInstance();
    $cache_mock = $this->getMockBuilder(ImageApi::class)
      ->setMethods(['getEventImage'])
      ->getMock();
    $_cache_property->setValue($instance, $cache_mock);
    return $cache_mock;
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_handleRequest_200()
  {
    $mock_result = getCachedImage('image/jpeg', '\imagejpeg');
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getEventImage')
      ->with(123)
      ->will($this->returnValue($mock_result));
    $request = new \WP_REST_Request('GET', '/yesticket/v1/picture/123');
    \ob_start();
    $response = @$this->server->dispatch($request);
    $output = \ob_end_clean();
    $this->assertSame(200, $response->get_status());
    $this->assertContains('Content-Type: image/jpeg', $response->get_headers());
    $this->assertNotEmpty($output);
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_handleRequest_given_exception_expect_redirect()
  {
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getEventImage')
      ->with(123)
      ->willThrowException(new ImageException('my-message', 503));
    $request = new \WP_REST_Request('GET', '/yesticket/v1/picture/123');
    \ob_start();
    $response = @$this->server->dispatch($request);
    $output = \ob_end_clean();
    $this->assertSame(307, $response->get_status(), "Fallback should be redirect!");
    $headers = $response->get_headers();
    $this->assertContains('Location: https://www.yesticket.org/dev/picture.php?event=123', $headers, 'Should send a Location Header!');
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

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_serveImage_already_served_expect_false()
  {
    $result = new \WP_REST_Response();
    $result->set_matched_route('/yesticket/v1/picture');
    $this->assertFalse(ImageEndpoint::getInstance()->servePicture(true, $result));
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_serveImage_is_error_expect_false()
  {
    $result = new \WP_REST_Response();
    $result->set_matched_route('/yesticket/v1/picture');
    $result->set_status(400);
    $this->assertFalse(ImageEndpoint::getInstance()->servePicture(false, $result));
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_serveImage_path_not_interesting_expect_false()
  {
    $result = new \WP_REST_Response();
    $result->set_matched_route('/other-namespace/v1/picture');
    $this->assertFalse(ImageEndpoint::getInstance()->servePicture(false, $result));
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_serveImage_result_not_cached_image_expect_false()
  {
    $result = new \WP_REST_Response();
    $result->set_matched_route('/yesticket/v1/picture');
    $result->set_data(array('a property' => 'a string'));
    $this->assertFalse(ImageEndpoint::getInstance()->servePicture(false, $result));
  }

  /**
   * @covers YesTicket\Rest\ImageEndpoint
   */
  function test_serveImage_works()
  {
    $result = new \WP_REST_Response();
    $result->set_matched_route('/yesticket/v1/picture');
    $result->set_headers(['some-header: withValue']);
    $result->set_data(getCachedImage('image/jpeg', '\imagejpeg', 100));
    \ob_start();
    $this->assertTrue(ImageEndpoint::getInstance()->servePicture(false, $result));
    $body = \ob_get_clean();
    $this->assertNotEmpty($body);
    $this->assertStringContainsString('quality = 100', $body);
  }
}
