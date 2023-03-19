<?php


use \YesTicket\ImageApi;
use \YesTicket\ImageCache;
use \YesTicket\Model\CachedImage;

// As seen in https://torquemag.io/2017/01/testing-api-endpoints/
class ImageApiTest extends WP_UnitTestCase
{

  static $pre_http_request_filter_has_run = false;
  static $external_call_url = '';

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
    // Define expected yesticket URL for image
    $expected_url = "https://www.yesticket.org/picture.php?event=123";
    // Set up mock to return a png on 'image/png'
    $mock_result = new CachedImage();
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with(
        $expected_url,
        $this->callback(function ($getFunction) {
          return \is_callable($getFunction);
        })
      )
      ->will($this->returnValue($mock_result));
    // Call and assert
    $request = new WP_REST_Request();
    $request['event_id'] = 123;
    $this->assertSame($mock_result, ImageApi::getInstance()->getEventImage($request));
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_returns_cachedImage_dev()
  {
    // Define expected yesticket URL for image
    $expected_url = "https://www.yesticket.org/dev/picture.php?event=123";
    // Set up mock to return a png on 'image/png'
    $mock_result = new CachedImage();
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with(
        $expected_url,
        $this->callback(function ($getFunction) {
          return \is_callable($getFunction);
        })
      )
      ->will($this->returnValue($mock_result));
    // Call and assert
    $request = new WP_REST_Request();
    $request['event_id'] = 123;
    $request['env'] = 'dev';
    $this->assertSame($mock_result, ImageApi::getInstance()->getEventImage($request));
  }

  /**
   * @covers YesTicket\ImageApi
   */
  function test_cache_returns_wp_error()
  {
    // Define our http-get endpoint
    $get_url = "https://www.yesticket.org/picture.php?event=123";
    // Set up mock to throw an ImageException
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once(0))
      ->method('getFromCacheOrFresh')
      ->with($get_url)
      ->will($this->returnValue(new WP_Error(503)));
    $request = new WP_REST_Request();
    $request['event_id'] = 123;
    $result = ImageApi::getInstance()->getEventImage($request);
    $this->assertTrue(\is_wp_error($result));
    $this->assertSame($get_url, $result->get_error_data(), "Error data should be the URL to the actual yesticket resource.");
  }

  /**
   * Construct a response to be used with filter
   * @see https://developer.wordpress.org/reference/hooks/pre_http_request/
   * 
   * @param array $headers of MockResponse
   * @param int $status_code of MockResponse
   * @param string $msg of MockResponse
   * @param string $body of MockResponse
   * @param int $success of MockResponse
   * @return array the MockResponse
   */
  private function mockHttpResponse($headers = [], $status_code = 200, $msg = 'OK', $body = '', $success = 1)
  {
    return array(
      'headers'     => new Requests_Utility_CaseInsensitiveDictionary($headers),
      'cookies'     => array(),
      'filename'    => null,
      'response'    => array('code' => $status_code, 'message' => $msg),
      'status_code' => $status_code,
      'success'     => $success,
      'body'        => $body,
    );
  }

  /**
   * Add Http Filter to mock WP_Http requests using short-circuit
   * 
   * @param array|WP_Error $mockResponse the MockResponse
   * 
   * @see https://developer.wordpress.org/reference/hooks/pre_http_request/
   */
  private function _preHttpRequestFilter($mockResponse)
  {
    ImageApiTest::$pre_http_request_filter_has_run = false;
    ImageApiTest::$external_call_url = '';
    // Setup MOCK for HTTP call
    remove_all_filters('pre_http_request', 69);
    \add_filter('pre_http_request', function ($preempt, $parsed_args, $url) use ($mockResponse) {
      ImageApiTest::$pre_http_request_filter_has_run = true;
      ImageApiTest::$external_call_url = $url;
      return $mockResponse;
    }, 69, 3);
  }

  /**
   * @covers YesTicket\ImageApi
   * 
   * Uses the Mock's validation possibility to call the function used to get the data.
   */
  function test_get_function()
  {
    // Define our http-get endpoint
    $event_id = 1;
    $expected_url = "https://www.yesticket.org/picture.php?event=$event_id";
    // Make our Mock validation run the passed function.
    $cache_mock = $this->initMock();
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with(
        $expected_url,
        $this->callback(function ($getFunction) use ($expected_url) {

          // >>>>>>>>>>>>>>>>>>> START actual test calls. <<<<<<<<<<<<<<<<<<<<<<
          // Error Cases
          //    related to 'HEAD' request
          $this->assertionsGivenWpError($getFunction, $expected_url);
          $this->assertionsGivenNoResponse($getFunction, $expected_url);
          $this->assertionsGivenNoHeaders($getFunction, $expected_url);
          $this->assertionsGivenNoResponseCode($getFunction, $expected_url);
          $this->assertionsGivenBadResponseCode($getFunction, $expected_url);
          $this->assertionsGivenNoContentTypeHeader($getFunction, $expected_url);
          $this->assertionsGivenBadContentTypeHeader($getFunction, $expected_url);

          //    related to content
          $emptyFile = \wp_tempnam();
          $fakePNGFile = \wp_tempnam();
          $fakeJPEGFile = \wp_tempnam();
          \imagepng(\imagecreatetruecolor(10, 10), $fakePNGFile);
          \imagejpeg(\imagecreatetruecolor(10, 10), $fakeJPEGFile);

          $this->assertionsGivenJPEGFileEmpty($getFunction, $emptyFile);
          $this->assertionsGivenPNGFileEmpty($getFunction, $emptyFile);
          $this->assertionsGivenJPEGisActuallyPNG($getFunction, $fakePNGFile);

          // Success cases
          $this->assertionsGivenJPEG($getFunction, $fakeJPEGFile);
          $this->assertionsGivenPNG($getFunction, $fakePNGFile);

          // >>>>>>>>>>>>>>>>>>> END actual test calls. <<<<<<<<<<<<<<<<<<<<<<
          return true;
        })
      )
      ->will($this->returnValue(new CachedImage()));
    // Start test.
    $request = new WP_REST_Request();
    $request['event_id'] = $event_id;
    ImageApi::getInstance()->getEventImage($request);
  }

  /**
   * Given 'HEAD' Request returns WP_Error
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenWpError($f, $expected_url)
  {
    \error_log("assertionsGivenWpError");
    $this->_preHttpRequestFilter(new WP_Error());
    // Call
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    // Check Mock was invoked
    $this->assertTrue(ImageApiTest::$pre_http_request_filter_has_run, "Should make HTTP call.");
    $this->assertSame($expected_url, ImageApiTest::$external_call_url, "Called wrong url");
    $this->assertTrue(\is_wp_error($result));
    $this->assertStringContainsString("WP_Error", $logged, "Should log the error.");
  }

  /**
   * Given 'HEAD' Request returns no 'response'
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenNoResponse($f, $expected_url)
  {
    \error_log("assertionsGivenNoResponse");
    $mockResponse = $this->mockHttpResponse();
    unset($mockResponse['response']);
    $this->_preHttpRequestFilter($mockResponse);
    // Call
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("Malformed response", $logged, "Should log the error.");
  }

  /**
   * Given 'HEAD' Request returns no 'headers'
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenNoHeaders($f, $expected_url)
  {
    \error_log("assertionsGivenNoHeaders");
    $mockResponse = $this->mockHttpResponse();
    unset($mockResponse['headers']);
    $this->_preHttpRequestFilter($mockResponse);
    // Call
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("Malformed response", $logged, "Should log the error.");
  }

  /**
   * Given 'HEAD' Request returns no 'response[code]'
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenNoResponseCode($f, $expected_url)
  {
    \error_log("assertionsGivenNoResponseCode");
    $mockResponse = $this->mockHttpResponse();
    unset($mockResponse['response']['code']);
    $this->_preHttpRequestFilter($mockResponse);
    // Call
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("has no response code", $logged, "Should log the error.");
  }

  /**
   * Given 'HEAD' Request response[code] is not 200
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenBadResponseCode($f, $expected_url)
  {
    \error_log("assertionsGivenBadResponseCode");
    $mockResponse = $this->mockHttpResponse([], 500);
    $this->_preHttpRequestFilter($mockResponse);
    // Call
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("Response code", $logged, "Should log the error.");
    $this->assertStringContainsString("not 200", $logged, "Should log the error.");
  }

  /**
   * Given 'HEAD' Request returns no 'content-type' header
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenNoContentTypeHeader($f, $expected_url)
  {
    \error_log("assertionsGivenNoContentTypeHeader");
    $mockResponse = $this->mockHttpResponse(array("another" => "header"));
    $this->_preHttpRequestFilter($mockResponse);
    // Call
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("no content-type header", $logged, "Should log the error.");
  }

  /**
   * Given 'HEAD' Request returns bad 'content-type' header
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenBadContentTypeHeader($f, $expected_url)
  {
    \error_log("assertionsGivenBadContentTypeHeader");
    $mockResponse = $this->mockHttpResponse(array("content-type" => "not-an-imag3"));
    $this->_preHttpRequestFilter($mockResponse);
    // Call
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("Content-type", $logged, "Should log the error.");
    $this->assertStringContainsString("not an image", $logged, "Should log the error.");
  }

  /**
   * Given 'HEAD' says it's a JPEG; Image file is bad
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenJPEGFileEmpty($f, $expected_url)
  {
    \error_log("assertionsGivenJPEGFileEmpty");
    $mockResponse = $this->mockHttpResponse(array("content-type" => "image/jpeg"));
    $this->_preHttpRequestFilter($mockResponse);
    LogCapture::start();
    $result = @$f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("image/jpeg", $logged, "Log should contain the content-type.");
  }

  /**
   * Given 'HEAD' says it's a PNG; Image file is bad
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenPNGFileEmpty($f, $expected_url)
  {
    \error_log("assertionsGivenPNGFileEmpty");
    $mockResponse = $this->mockHttpResponse(array("content-type" => "image/png"));
    $this->_preHttpRequestFilter($mockResponse);
    LogCapture::start();
    $result = @$f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("image/png", $logged, "Log should contain the content-type.");
  }

  /**
   * Given 'HEAD' says it's a JPEG; is actually PNG Image
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenJPEGisActuallyPNG($f, $expected_url)
  {
    \error_log("assertionsGivenJPEGisActuallyPNG");
    $mockResponse = $this->mockHttpResponse(array("content-type" => "image/jpeg"));
    $this->_preHttpRequestFilter($mockResponse);
    LogCapture::start();
    $result = @$f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("image/jpeg", $logged, "Log should contain the content-type.");
  }

  /**
   * Given 'HEAD' says it's a JPEG; is actually PNG Image
   * Expect: WP_Error
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenBadJPEGFile($f, $expected_url)
  {
    \error_log("assertionsGivenBadJPEGFile");
    $mockResponse = $this->mockHttpResponse(array("content-type" => "image/jpeg"));
    $this->_preHttpRequestFilter($mockResponse);
    LogCapture::start();
    $result = $f($expected_url);
    $logged = LogCapture::end_get();
    $this->assertTrue(\is_wp_error($result), "Expected WP_Error");
    $this->assertStringContainsString($expected_url, $logged, "Log should contain the URL.");
    $this->assertStringContainsString("Could not render image", $logged);
    $this->assertStringContainsString("image/jpeg", $logged, "Log should contain the content-type.");
  }

  /**
   * Given 'HEAD' says it's a JPEG; is actually PNG Image
   * Expect: CachedImage
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenJPEG($f, $expected_url)
  {
    \error_log("assertionsGivenJPEG");
    $mockResponse = $this->mockHttpResponse(array("content-type" => "image/jpeg"));
    $this->_preHttpRequestFilter($mockResponse);
    $result = $f($expected_url);
    $this->assertTrue($result instanceof CachedImage, "Expected CachedImage");
    $this->assertSame("image/jpeg", $result->get_content_type());
    $this->assertNotEmpty($result->get_image_data());
  }

  /**
   * Given 'HEAD' says it's a JPEG; is actually PNG Image
   * Expect: CachedImage
   * 
   * @param callable $f the function
   * @param string $expected_url of the resource
   */
  private function assertionsGivenPNG($f, $expected_url)
  {
    \error_log("assertionsGivenPNG");
    $mockResponse = $this->mockHttpResponse(array("content-type" => "image/png"));
    $this->_preHttpRequestFilter($mockResponse);
    $result = $f($expected_url);
    $this->assertTrue($result instanceof CachedImage, "Expected CachedImage");
    $this->assertSame("image/png", $result->get_content_type());
    $this->assertNotEmpty($result->get_image_data());
  }
}
