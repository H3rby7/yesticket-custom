<?php

namespace YesTicket;

use \YesTicket\ImageApi;
use \YesTicket\ImageCache;
use \YesTicket\WrongImageTypeException;

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
    $_class = new \ReflectionClass(ImageApi::class);
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
    $_cache_property = new \ReflectionProperty(ImageApi::class, "cache");
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
  function test_cache_returns_jpeg()
  {
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    \delete_transient(ImageCache::getInstance()->cacheKey($get_url));
    $mock_result = getCachedImage('image/jpeg', '\imagejpeg', 100);
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->will($this->returnValue($mock_result));
    $response = ImageApi::getInstance()->getEventImage(123);
    $this->assertNotEmpty($response);
    $this->assertSame("image/jpeg", $response->content_type);
    $this->assertStringContainsString('quality = 100', $response->image_data);
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_throws_then_returns_png()
  {
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    \delete_transient(ImageCache::getInstance()->cacheKey($get_url));
    $mock_result = getCachedImage('image/png', '\imagepng', 0);
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->at(0))
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->willThrowException(new WrongImageTypeException());
    $cache_mock->expects($this->at(1))
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->will($this->returnValue($mock_result));
    $response = ImageApi::getInstance()->getEventImage(123);
    $this->assertNotEmpty($response);
    $this->assertSame("image/png", $response->content_type);
  }


}
