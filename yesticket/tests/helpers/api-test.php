<?php

namespace YesTicket;
use \InvalidArgumentException;

class ApiTest extends \WP_UnitTestCase
{
  function test_class_exists()
  {
    $this->assertTrue(class_exists("YesTicket\Api"));
  }

  /**
   * @covers YesTicket\Api
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(Api::getInstance());
  }

  /**
   * Initiate Mock for @see Cache
   * 
   * @param string $expected_url
   * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expected_times
   * @return string the response the mock will return
   */
  private function initMock($expected_url)
  {
    $_cache_property = new \ReflectionProperty(Api::class, "cache");
    $_cache_property->setAccessible(true);
    $instance = Api::getInstance();
    $cache_mock = $this->getMockBuilder(Cache::class)
      ->setMethods(['getFromCacheOrFresh'])
      ->getMock();
    $_cache_property->setValue($instance, $cache_mock);
    $mock_result = 'mocked-body';
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with($expected_url)
      ->will($this->returnValue($mock_result));
    return $mock_result;
  }

  /**
   * Prepare Mocks, filters and options for call
   */
  private function prepare($locale = 'en_EN', $opt_organizer = NULL, $opt_key = NULL, $expected)
  {
    // Mock locale
    add_filter('locale', function () use (&$locale) {
      return $locale;
    });
    update_option('yesticket_settings_required', array(
      'organizer_id' => $opt_organizer,
      'api_key' => $opt_key,
    ));
    // Prepare
    return $this->initMock($expected);
  }

  private function run_events($locale = 'en_EN', $att = array(), $opt_organizer = NULL, $opt_key = NULL, $expected)
  {
    $mock_result = $this->prepare($locale, $opt_organizer, $opt_key, $expected);
    $result = Api::getInstance()->getEvents($att);
    $this->assertSame($mock_result, $result);
  }

  private function run_testimonials($locale = 'en_EN', $att = array(), $opt_organizer = NULL, $opt_key = NULL, $expected)
  {
    $mock_result = $this->prepare($locale, $opt_organizer, $opt_key, $expected);
    $result = Api::getInstance()->getTestimonials($att);
    $this->assertSame($mock_result, $result);
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEvents()
  {
    $base_uri = 'https://www.yesticket.org/api/v2/events.php';
    // Basic
    $this->run_events('en_EN', array(), '1', 'key1', "$base_uri?lang=en&organizer=1&key=key1");
    // Defaults of shortcode
    $this->run_events('en_EN', array(
      'env' => NULL,
      'api-version' => NULL,
      'organizer' => NULL,
      'key' => NULL,
      'type' => 'all',
      'count' => 9,
      'grep' => NULL,
    ), '1', 'key1', "$base_uri?count=9&type=all&lang=en&organizer=1&key=key1");
    // locale de_DE
    $this->run_events('de_DE', array(), '1', 'key1', "$base_uri?lang=de&organizer=1&key=key1");
    // Different organizer & key
    $this->run_events('en_EN', array('organizer' => '2', 'key' => 'keyof2'), '1', 'key1', "$base_uri?lang=en&organizer=2&key=keyof2");
    // env = dev
    $this->run_events('en_EN', array('env' => 'dev'), '1', 'key1', "https://www.yesticket.org/dev/api/v2/events.php?lang=en&organizer=1&key=key1");
    // count = 50
    $this->run_events('en_EN', array('count' => '50'), '1', 'key1', "$base_uri?count=50&lang=en&organizer=1&key=key1");
    // type = all
    $this->run_events('en_EN', array('type' => 'all'), '1', 'key1', "$base_uri?type=all&lang=en&organizer=1&key=key1");
    // api-version = 1
    $this->run_events('en_EN', array('api-version' => '1'), '1', 'key1', "https://www.yesticket.org/api/events-endpoint.php?lang=en&organizer=1&key=key1");
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonials()
  {
    $base_uri = 'https://www.yesticket.org/api/v2/testimonials.php';
    // Basic
    $this->run_testimonials('en_EN', array(), '1', 'key1', "$base_uri?lang=en&organizer=1&key=key1");
    // Defaults of shortcode
    $this->run_testimonials('en_EN', array(
      'env' => NULL,
      'api-version' => NULL,
      'organizer' => NULL,
      'key' => NULL,
      'type' => 'all',
      'count' => '9',
    ), '1', 'key1', "$base_uri?count=9&type=all&lang=en&organizer=1&key=key1");
    // locale de_DE
    $this->run_testimonials('de_DE', array(), '1', 'key1', "$base_uri?lang=de&organizer=1&key=key1");
    // Different organizer & key
    $this->run_testimonials('en_EN', array('organizer' => '2', 'key' => 'keyof2'), '1', 'key1', "$base_uri?lang=en&organizer=2&key=keyof2");
    // env = dev
    $this->run_testimonials('en_EN', array('env' => 'dev'), '1', 'key1', "https://www.yesticket.org/dev/api/v2/testimonials.php?lang=en&organizer=1&key=key1");
    // count = 50
    $this->run_testimonials('en_EN', array('count' => '50'), '1', 'key1', "$base_uri?count=50&lang=en&organizer=1&key=key1");
    // types
    $this->run_testimonials('en_EN', array('type' => 'all'), '1', 'key1', "$base_uri?type=all&lang=en&organizer=1&key=key1");
    $this->run_testimonials('en_EN', array('type' => 'performance'), '1', 'key1', "$base_uri?type=performance&lang=en&organizer=1&key=key1");
    $this->run_testimonials('en_EN', array('type' => 'workshop'), '1', 'key1', "$base_uri?type=workshop&lang=en&organizer=1&key=key1");
    $this->run_testimonials('en_EN', array('type' => 'festival'), '1', 'key1', "$base_uri?type=festival&lang=en&organizer=1&key=key1");
    // api-version = 1
    $this->run_testimonials('en_EN', array('api-version' => '1'), '1', 'key1', "https://www.yesticket.org/api/testimonials-endpoint.php?lang=en&organizer=1&key=key1");
  }

  private function run_events_forThrows($req_settings, $att, $exception, $exception_msg)
  {
    add_filter('locale', function () {
      return 'en_EN';
    });
    update_option('yesticket_settings_required', $req_settings);
    $this->expectException($exception);
    $this->expectExceptionMessage($exception_msg);
    Api::getInstance()->getEvents($att);
  }

  private function run_testimonials_forThrows($req_settings, $att, $exception, $exception_msg)
  {
    add_filter('locale', function () {
      return 'en_EN';
    });
    update_option('yesticket_settings_required', $req_settings);
    $this->expectException($exception);
    $this->expectExceptionMessage($exception_msg);
    Api::getInstance()->getTestimonials($att);
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEventsMissingOrganizer_expectThrows()
  {
    $this->run_events_forThrows(
      array('organizer_id' => NULL, 'api_key' => NULL),
      array('key' => 'anyApiKey'),
      InvalidArgumentException::class,
      "Please configure your 'organizer-id'"
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEventsMissingApiKey_expectThrows()
  {
    $this->run_events_forThrows(
      array('organizer_id' => NULL, 'api_key' => NULL),
      array('organizer' => '1'),
      InvalidArgumentException::class,
      "Please configure your 'key'"
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEventsInvalidType_expectThrows()
  {
    $this->run_events_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('type' => 'an-invalid-type'),
      InvalidArgumentException::class,
      "Please provide a valid 'type'"
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEventsApiVersionNonNumeric_expectThrows()
  {
    $this->run_events_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('api-version' => 'a string'),
      InvalidArgumentException::class,
      '"api-version" must be an int bigger or equal to 1 and smaller or equal to'
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEventsApiVersionLowerThan1_expectThrows()
  {
    $this->run_events_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('api-version' => 0),
      InvalidArgumentException::class,
      '"api-version" must be an int bigger or equal to 1 and smaller or equal to'
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEventsApiVersionBiggerThan2_expectThrows()
  {
    $this->run_events_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('api-version' => '3'),
      InvalidArgumentException::class,
      '"api-version" must be an int bigger or equal to 1 and smaller or equal to'
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonialsMissingOrganizer_expectThrows()
  {
    $this->run_testimonials_forThrows(
      array('organizer_id' => NULL, 'api_key' => NULL),
      array('key' => 'anyApiKey'),
      InvalidArgumentException::class,
      "Please configure your 'organizer-id'"
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonialsMissingApiKey_expectThrows()
  {
    $this->run_testimonials_forThrows(
      array('organizer_id' => NULL, 'api_key' => NULL),
      array('organizer' => '1'),
      InvalidArgumentException::class,
      "Please configure your 'key'"
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonialsInvalidType_expectThrows()
  {
    $this->run_testimonials_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('type' => 'an-invalid-type'),
      InvalidArgumentException::class,
      "Please provide a valid 'type'"
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonialsApiVersionNonNumeric_expectThrows()
  {
    $this->run_testimonials_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('api-version' => 'a string'),
      InvalidArgumentException::class,
      '"api-version" must be an int bigger or equal to 1 and smaller or equal to'
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonialsApiVersionLowerThan1_expectThrows()
  {
    $this->run_testimonials_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('api-version' => 0),
      InvalidArgumentException::class,
      '"api-version" must be an int bigger or equal to 1 and smaller or equal to'
    );
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonialsApiVersionBiggerThan2_expectThrows()
  {
    $this->run_testimonials_forThrows(
      array('organizer_id' => '1', 'api_key' => 'key1'),
      array('api-version' => '3'),
      InvalidArgumentException::class,
      '"api-version" must be an int bigger or equal to 1 and smaller or equal to'
    );
  }
}
