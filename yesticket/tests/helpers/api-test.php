<?php

use \YesTicket\Api;
use \YesTicket\PluginOptions;
use \YesTicket\RestCache;
use \YesTicket\Model\Event;

class ApiTest extends WP_UnitTestCase
{
  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Api"));
  }

  /**
   * @covers YesTicket\Api
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(Api::getInstance());
  }

  /**
   * Initiate Mock for @see RestCache
   * 
   * @param string $expected_url
   * @param mixed $mock_result
   */
  private function initMock($expected_url, $mock_result)
  {
    // Inject Mock into API::$instance
    $_cache_property = new ReflectionProperty(Api::class, "cache");
    $_cache_property->setAccessible(true);
    $instance = Api::getInstance();
    $cache_mock = $this->getMockBuilder(RestCache::class)
      ->setMethods(['getFromCacheOrFresh'])
      ->getMock();
    $_cache_property->setValue($instance, $cache_mock);

    // Set up mock for call
    $cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with($expected_url)
      ->will($this->returnValue(\json_encode($mock_result)));
  }

  /**
   * Prepare Mocks, filters and options for call
   */
  private function prepare($expected, $mock_result, $opt_organizer = NULL, $opt_key = NULL, $locale = 'en_EN')
  {
    // Mock locale
    \add_filter('locale', function () use (&$locale) {
      return $locale;
    });
    \update_option(PluginOptions::SETTINGS_REQUIRED_KEY, array(
      'organizer_id' => $opt_organizer,
      'api_key' => $opt_key,
    ));
    // Prepare
    $this->initMock($expected, $mock_result);
  }

  private function run_events($expected, $att = array(), $opt_organizer = NULL, $opt_key = NULL, $locale = 'en_EN')
  {
    // Generate a Mock Result
    $event_uses_cache = \filter_var(\ini_get('allow_url_fopen'), \FILTER_VALIDATE_BOOLEAN);
    $evt1 = new Event($event_uses_cache);
    $evt1->event_name = "My mocked event #1";
    $evt2 = new Event($event_uses_cache);
    $evt2->event_name = "My other mocked event (#2)";
    $mock_result = array($evt1, $evt2);

    $this->prepare($expected, $mock_result, $opt_organizer, $opt_key, $locale);
    $result = Api::getInstance()->getEvents($att);
    $this->assertEqualSets($mock_result, $result, "Should be equal.");
  }

  private function run_testimonials($expected, $att = array(), $opt_organizer = NULL, $opt_key = NULL, $locale = 'en_EN')
  {
    $mock_result = array(\json_decode("{'event_name': 'something'}"));
    $this->prepare($expected, $mock_result, $opt_organizer, $opt_key, $locale);
    $result = Api::getInstance()->getTestimonials($att);
    $this->assertEqualSets($mock_result, $result);
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getEvents()
  {
    $base_uri = 'https://www.yesticket.org/api/v2/events.php';
    // Basic
    $this->run_events("$base_uri?lang=en&organizer=1&key=key1", array(), '1', 'key1', 'en_EN');
    // Defaults of shortcode
    $this->run_events("$base_uri?count=9&type=all&lang=en&organizer=1&key=key1", array(
      'env' => NULL,
      'api-version' => NULL,
      'organizer' => NULL,
      'key' => NULL,
      'type' => 'all',
      'count' => 9,
      'grep' => NULL,
    ), '1', 'key1', 'en_EN');
    // locale de_DE
    $this->run_events("$base_uri?lang=de&organizer=1&key=key1", array(), '1', 'key1', 'de_DE');
    // Different organizer & key
    $this->run_events("$base_uri?lang=en&organizer=2&key=keyof2", array('organizer' => '2', 'key' => 'keyof2'), '1', 'key1', 'en_EN');
    // env = dev
    $this->run_events("https://www.yesticket.org/dev/api/v2/events.php?lang=en&organizer=1&key=key1", array('env' => 'dev'), '1', 'key1', 'en_EN');
    // count = 50
    $this->run_events("$base_uri?count=50&lang=en&organizer=1&key=key1", array('count' => '50'), '1', 'key1', 'en_EN');
    // type = all
    $this->run_events("$base_uri?type=all&lang=en&organizer=1&key=key1", array('type' => 'all'), '1', 'key1', 'en_EN');
    // api-version = 1
    $this->run_events("https://www.yesticket.org/api/events-endpoint.php?lang=en&organizer=1&key=key1", array('api-version' => '1'), '1', 'key1', 'en_EN');

    // Generate a Mock Result
    $evt1 = new Event(false);
    $evt1->event_name = "My mocked event #1";
    $evt2 = new Event(false);
    $evt2->event_name = "My other mocked event (#2)";
    $mock_result = array($evt1, $evt2);

    // grep = 'mocked'
    $this->prepare("$base_uri?lang=en&organizer=1&key=key1", $mock_result, '1', 'key1', 'en_EN');
    $result = Api::getInstance()->getEvents(array('grep' => 'mocked'));
    $this->assertCount(2, $result);
    // grep = '#1'
    $this->prepare("$base_uri?lang=en&organizer=1&key=key1", $mock_result, '1', 'key1', 'en_EN');
    $result = Api::getInstance()->getEvents(array('grep' => '#1'));
    $this->assertCount(1, $result);
    $this->assertSame('My mocked event #1', $result[0]->event_name);
  }

  /**
   * @covers YesTicket\Api
   */
  function test_getTestimonials()
  {
    $base_uri = 'https://www.yesticket.org/api/v2/testimonials.php';
    // Basic
    $this->run_testimonials("$base_uri?lang=en&organizer=1&key=key1", array(), '1', 'key1', 'en_EN');
    // Defaults of shortcode
    $this->run_testimonials("$base_uri?count=9&type=all&lang=en&organizer=1&key=key1", array(
      'env' => NULL,
      'api-version' => NULL,
      'organizer' => NULL,
      'key' => NULL,
      'type' => 'all',
      'count' => '9',
    ), '1', 'key1', 'en_EN');
    // locale de_DE
    $this->run_testimonials("$base_uri?lang=de&organizer=1&key=key1", array(), '1', 'key1', 'de_DE');
    // Different organizer & key
    $this->run_testimonials("$base_uri?lang=en&organizer=2&key=keyof2", array('organizer' => '2', 'key' => 'keyof2'), '1', 'key1', 'en_EN');
    // env = dev
    $this->run_testimonials("https://www.yesticket.org/dev/api/v2/testimonials.php?lang=en&organizer=1&key=key1", array('env' => 'dev'), '1', 'key1', 'en_EN');
    // count = 50
    $this->run_testimonials("$base_uri?count=50&lang=en&organizer=1&key=key1", array('count' => '50'), '1', 'key1', 'en_EN');
    // types
    $this->run_testimonials("$base_uri?type=all&lang=en&organizer=1&key=key1", array('type' => 'all'), '1', 'key1', 'en_EN');
    $this->run_testimonials("$base_uri?type=performance&lang=en&organizer=1&key=key1", array('type' => 'performance'), '1', 'key1', 'en_EN');
    $this->run_testimonials("$base_uri?type=workshop&lang=en&organizer=1&key=key1", array('type' => 'workshop'), '1', 'key1', 'en_EN');
    $this->run_testimonials("$base_uri?type=festival&lang=en&organizer=1&key=key1", array('type' => 'festival'), '1', 'key1', 'en_EN');
    // api-version = 1
    $this->run_testimonials("https://www.yesticket.org/api/testimonials-endpoint.php?lang=en&organizer=1&key=key1", array('api-version' => '1'), '1', 'key1', 'en_EN');
  }

  private function run_events_forThrows($req_settings, $att, $exception, $exception_msg)
  {
    \add_filter('locale', function () {
      return 'en_EN';
    });
    \update_option(PluginOptions::SETTINGS_REQUIRED_KEY, $req_settings);
    $this->expectException($exception);
    $this->expectExceptionMessage($exception_msg);
    Api::getInstance()->getEvents($att);
  }

  private function run_testimonials_forThrows($req_settings, $att, $exception, $exception_msg)
  {
    \add_filter('locale', function () {
      return 'en_EN';
    });
    \update_option(PluginOptions::SETTINGS_REQUIRED_KEY, $req_settings);
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
