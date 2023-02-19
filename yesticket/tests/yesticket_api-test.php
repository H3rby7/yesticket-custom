<?php

class YesTicketApiTest extends WP_UnitTestCase
{
  private $_cache_mock = null;

  protected function setUp(): void
  {
    $_cache_property = new ReflectionProperty(YesTicketApi::class, "cache");
    $_cache_property->setAccessible(true);
    $instance = YesTicketApi::getInstance();
    $this->_cache_mock = $this->getMockBuilder(YesTicketCache::class)
      ->setMethods(['getFromCacheOrFresh'])
      ->getMock();
    $_cache_property->setValue($instance, $this->_cache_mock);
  }

  protected function tearDown(): void
  {
    // TODO
    $att = array(
      'env' => 'prod',
      'api-version' => '',
      'organizer' => '1',
      'key' => 'key1',
      'type' => 'all',
      'count' => '100',
    );
  }

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

  private function expect($url)
  {
    $mock_result = 'mocked-body';
    $this->_cache_mock->expects($this->once())
      ->method('getFromCacheOrFresh')
      ->with($url)
      ->will($this->returnValue($mock_result));
    return $mock_result;
  }

  /**
   * @covers YesTicketApi::getEvents
   */
  function test_getEvents_organizer_and_key_set_in_att()
  {
    // Mock locale
    add_filter('locale', function () {
      return 'en_EN';
    });
    $att = array(
      'organizer' => '1',
      'key' => 'key1',
    );
    // Prepare our mock
    $mock_result = $this->expect("https://www.yesticket.org/api/v2/events.php?lang=en&organizer=1&key=key1");

    $result = YesTicketApi::getInstance()->getEvents($att);
    $this->assertSame($mock_result, $result);
  }

  /**
   * @covers YesTicketApi::getEvents
   */
  function test_getEvents_organizer_and_key_set_in_att_locale_de_DE()
  {
    // Mock locale
    add_filter('locale', function () {
      return 'de_DE';
    });
    $att = array(
      'organizer' => '2',
      'key' => 'keyof2',
    );
    // Prepare our mock
    $mock_result = $this->expect("https://www.yesticket.org/api/v2/events.php?lang=de&organizer=2&key=keyof2");
    
    $result = YesTicketApi::getInstance()->getEvents($att);
    $this->assertSame($mock_result, $result);
  }

  /**
   * @covers YesTicketApi::getEvents
   */
  function test_getEvents_all_set_in_att_env_dev()
  {
    // Mock locale
    add_filter('locale', function () {
      return 'en_EN';
    });
    $att = array(
      'env' => 'dev',
      'organizer' => '1',
      'key' => 'key1',
      'type' => 'all',
      'count' => '100',
    );
    // Prepare our mock
    $mock_result = $this->expect("https://www.yesticket.org/dev/api/v2/events.php?count=100&type=all&lang=en&organizer=1&key=key1");
    
    $result = YesTicketApi::getInstance()->getEvents($att);
    $this->assertSame($mock_result, $result);
  }

  /**
   * @covers YesTicketApi::getEvents
   */
  function test_getEvents_organizer_and_key_set_in_att_api_v1()
  {
    // Mock locale
    add_filter('locale', function () {
      return 'en_EN';
    });
    $att = array(
      'api-version' => '1',
      'organizer' => '1',
      'key' => 'key1',
    );
    // Prepare our mock
    $mock_result = $this->expect("https://www.yesticket.org/api/events-endpoint.php?lang=en&organizer=1&key=key1");
    
    $result = YesTicketApi::getInstance()->getEvents($att);
    $this->assertSame($mock_result, $result);
  }

  /**
   * @covers YesTicketApi::getEvents
   */
  function test_getEvents_organizer_and_key_set_in_options()
  {
    // Mock locale
    add_filter('locale', function () {
      return 'en_EN';
    });
    $att = array();
    // TODO: update_options!
    
    // Prepare our mock
    $mock_result = $this->expect("https://www.yesticket.org/api/events-endpoint.php?lang=en&organizer=1&key=key1");
    
    $result = YesTicketApi::getInstance()->getEvents($att);
    $this->assertSame($mock_result, $result);
  }
}
