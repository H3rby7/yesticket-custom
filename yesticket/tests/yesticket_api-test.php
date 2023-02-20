<?php

class YesTicketApiTest extends WP_UnitTestCase
{
  function test_class_exists()
  {
    $this->assertTrue(class_exists("YesTicketApi"));
  }

  /**
   * @covers YesTicketApi::getInstance
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(YesTicketApi::getInstance());
  }

  private function initMock($expected_url)
  {
    $_cache_property = new ReflectionProperty(YesTicketApi::class, "cache");
    $_cache_property->setAccessible(true);
    $instance = YesTicketApi::getInstance();
    $cache_mock = $this->getMockBuilder(YesTicketCache::class)
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

  private function run_events($locale = 'en_EN', $att = array(), $opt_organizer = NULL, $opt_key = NULL, $expected)
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
    $mock_result = $this->initMock($expected);

    $result = YesTicketApi::getInstance()->getEvents($att);
    $this->assertSame($mock_result, $result);
  }

  /**
   * @covers YesTicketApi::getEvents
   */
  function test_getEvents()
  {
    $base_uri = 'https://www.yesticket.org/api/v2/events.php';
    // Basic
    $this->run_events('en_EN', array(), '1', 'key1', "$base_uri?lang=en&organizer=1&key=key1");
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
}
