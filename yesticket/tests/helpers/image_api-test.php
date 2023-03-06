<?php

namespace YesTicket;

use \YesTicket\ImageApi;
use \YesTicket\ImageCache;
use \YesTicket\WrongImageTypeException;
use \YesTicket\ImageException;
use \LogCapture;

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
   * Utility function to simplify different fetch function validations
   * Validate that the passed argument $f is a function and can fetch a 'whatever $renderer' returns.
   * @param callable $f the function
   * @param callable $renderer one of \imageXXXX
   * @return boolean true if valid; false if invalid
   */
  private function _validateFetchFunction($f, $renderer)
  {
    if (!\is_callable($f)) {
      \error_log('Must be a callable!');
      return false;
    }
    // Create fake resource to be fetched
    $fakeResourceFile = \wp_tempnam();
    $renderer(\imagecreatetruecolor(10, 10), $fakeResourceFile);
    // Fetch fake resource
    $result = $f($fakeResourceFile);
    // Assertions
    return $result !== FALSE && (\is_resource($result) || $result instanceof \GdImage);
  }

  /**
   * Validate that the passed argument is a function and can fetch a jpeg
   * @param callable $f the function
   * @return boolean true if valid; false if invalid
   */
  private function validateJPEGFetchFunction($f)
  {
    return $this->_validateFetchFunction($f, '\imagejpeg');
  }

  /**
   * Validate that the passed argument is a function and can fetch a png
   * @param callable $f the function
   * @return boolean true if valid; false if invalid
   */
  private function validatePNGFetchFunction($f)
  {
    return $this->_validateFetchFunction($f, '\imagepng');
  }

  /**
   * Validate that the passed argument is a function and can render an image
   * @param callable $f the function
   * @return boolean true if valid; false if invalid
   */
  private function validateRenderFunction($f)
  {
    if (!\is_callable($f)) {
      \error_log('Must be a callable!');
      return false;
    }
    \ob_start();
    $this->assertTrue($f(\imagecreatetruecolor(10, 10)));
    return !empty(\ob_get_clean());
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_returns_jpeg()
  {
    // Define our http-get endpoint
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    // Set up mock to return a png on 'image/png'
    $mock_result = getCachedImage('image/jpeg', '\imagejpeg', 100);
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with(
        $get_url,
        'image/jpeg',
        $this->callback(function ($fetchFunction) {
          return $this->validateJPEGFetchFunction($fetchFunction);
        }),
        $this->callback(function ($renderFunction) {
          return $this->validateRenderFunction($renderFunction);
        })
      )
      ->will($this->returnValue($mock_result));
    // Call and assert
    $response = ImageApi::getInstance()->getEventImage(123);
    $this->assertNotEmpty($response);
    $this->assertSame("image/jpeg", $response->get_content_type());
    $this->assertStringContainsString('quality = 100', $response->get_image_data());
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_throws_then_returns_png()
  {
    // Define our http-get endpoint
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    // Set up mock to ...
    $mock_result = getCachedImage('image/png', '\imagepng', 0);
    $cache_mock = $this->initMock();
    //                ... throw WrongImageTypeException on first call with 'image/jpeg'
    $cache_mock->expects($this->at(0))
      ->method('getFromCacheOrFresh')
      ->with(
        $get_url,
        'image/jpeg'
      )
      ->willThrowException(new WrongImageTypeException());
    //                ... return a png on second call with 'image/png'
    $cache_mock->expects($this->at(1))
      ->method('getFromCacheOrFresh')
      ->with(
        $get_url,
        'image/png',
        $this->callback(function ($fetchFunction) {
          return $this->validatePNGFetchFunction($fetchFunction);
        }),
        $this->callback(function ($renderFunction) {
          return $this->validateRenderFunction($renderFunction);
        })
      )
      ->will($this->returnValue($mock_result));
    // Call and assert
    $response = ImageApi::getInstance()->getEventImage(123);
    $this->assertNotEmpty($response);
    $this->assertSame("image/png", $response->get_content_type());
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_throws_image_exception_with_message()
  {
    // Define our http-get endpoint
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    // Set up mock to throw an ImageException
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once(0))
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->willThrowException(new ImageException('mock does not approve this call'));
    // Start Captures
    $didThrow = false;
    LogCapture::start();
    try {
      ImageApi::getInstance()->getEventImage(123);
    } catch (ImageException $e) {
      // We expect to get here
      $didThrow = true;
      $this->assertStringContainsString('mock does not approve this call', $e->getMessage());
    } finally {
      $logged = LogCapture::end_get();
      $this->assertStringContainsString('mock does not approve this call', $logged);
      // Safety, if we did not catch an error $didThrow will still be false and the assertion fails.
      $this->assertTrue($didThrow, 'Expected ImageException');
    }
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_throws_image_exception_no_message()
  {
    // Define our http-get endpoint
    $get_url = "https://www.yesticket.org/dev/picture.php?event=123";
    // Set up mock to throw an ImageException
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once(0))
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->willThrowException(new ImageException());
    // Start Captures
    $didThrow = false;
    LogCapture::start();
    try {
      ImageApi::getInstance()->getEventImage(123);
    } catch (ImageException $e) {
      // We expect to get here
      $didThrow = true;
    } finally {
      $logged = LogCapture::end_get();
      $this->assertStringContainsString('Unknown Error', $logged);
      $this->assertStringContainsString($get_url, $logged, "Should log the URL");
      // Safety, if we did not catch an error $didThrow will still be false and the assertion fails.
      $this->assertTrue($didThrow, 'Expected ImageException');
    }
  }
}
