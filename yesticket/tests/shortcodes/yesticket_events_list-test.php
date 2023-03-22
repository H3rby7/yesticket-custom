<?php

use \YesTicket\Api;
use \YesTicket\EventsList;
use YesTicket\Model\Event;

include_once(__DIR__ . "/../utility.php");

class EventsListTest extends YTP_TranslateTestCase
{

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\EventsList"));
  }

  /**
   * Initiate Mock for @see Api
   */
  private function initMock()
  {
    // Inject Mock into API::$instance
    $_cache_property = new ReflectionProperty(EventsList::class, "api");
    $_cache_property->setAccessible(true);
    $instance = EventsList::getInstance();
    $api_mock = $this->getMockBuilder(Api::class)
      ->setMethods(['getEvents'])
      ->getMock();
    $_cache_property->setValue($instance, $api_mock);
    return $api_mock;
  }

  /**
   * @covers YesTicket\EventsList
   * @covers YesTicket\EventUsingShortcode
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(EventsList::getInstance());
  }

  function test_shortcode_no_events()
  {
    // Mock API
    $this->initMock();
    // Translations
    $expectedContent = $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = EventsList::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-list', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
  }

  function test_shortcode_exception()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using the defaults
      ->with($this->anything())
      ->will($this->throwException(new InvalidArgumentException("api-key not set!")));
    // Call shortcode
    $result = EventsList::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString("api-key not set!", $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-list', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
  }

  function test_shortcode_message_no_items_found()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using the defaults
      ->with($this->anything())
      ->will($this->returnValue(json_decode('{"message":"no items found"}')));
    // Translations
    $expectedContent = $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = EventsList::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-list', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
  }

  function test_shortcode_defaults()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockEvent()];
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using the defaults
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'all', 'count' => 100, 'theme' => 'light', 'grep' => NULL, 'ticketlink' => 'no',)))
      ->will($this->returnValue($mock_result));
    // Translations
    $expectedType = $this->expectTranslate("Performance");
    $this->expectTranslate("F j, Y");
    $this->expectTranslate("g:i A");
    // Call shortcode
    $result = EventsList::shortCode([]);
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-list', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-dark', $asXML);
    // Is an 'OL'
    $this->assertNotEmpty($asXML->xpath('ol'), "Should contain an ordered list.");
    // Check on our item.
    $item = $asXML->xpath('ol/li')[0];
    $this->assertNotEmpty($item, "Ordered list should contain an item.");
    $this->assertHtmlHasClass('ytp-event-list-row', $item);
    $this->assertHtmlContainsText($expectedType, $item, "Item should display its type, because \$att type is 'all'.");
    $this->assertHtmlContainsText("March 20, 2161", $item, "Item should show the date.");
    $this->assertHtmlContainsText("8:00 PM", $item, "Item should show the time.");
    $this->assertHtmlContainsText("my test event 2161", $item, "Item should show the event_name.");
    $this->assertHtmlContainsText("My amazing stage 2161", $item, "Item should show the location_name.");
    $this->assertHtmlContainsText("Paradise City 2161", $item, "Item should show the location_city.");
    $this->assertHtmlDoesNotContainText("https://link-to-my-tickets", $item, "Item should not have ticketlink, as \$att ticketlink is 'no'.");
  }

  function test_shortcode_with_type_ticketlink_and_theme()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockEvent()];
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using the defaults
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'performance', 'count' => 100, 'theme' => 'dark', 'grep' => NULL, 'ticketlink' => 'yes',)))
      ->will($this->returnValue($mock_result));
    // Translations
    $this->expectTranslate("Tickets");
    $this->expectTranslate("F j, Y");
    $this->expectTranslate("g:i A");
    // Call shortcode
    $result = EventsList::shortCode(array('type' => 'performance', 'theme' => 'dark', 'ticketlink' => 'yes',));
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-list', $asXML);
    $this->assertHtmlHasClass('ytp-dark', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-light', $asXML);
    // Check on our item.
    $item = $asXML->xpath('ol/li')[0];
    $this->assertHtmlDoesNotContainText("Performance", $item, "Item should NOT display its type, because \$att type is 'performance'.");
    $this->assertHtmlContainsText("https://link-to-my-tickets", $item, "Item should display the ticketlink, because \$att ticketlink is 'yes'.");
  }

  function test_shortcode_set_all_att()
  {
    $input_att = array(
      'env' => 'dev',
      'api-version' => 2,
      'organizer' => 16,
      'key' => 'an-api-key-for-16',
      'type' => 'performance',
      'count' => 69,
      'theme' => 'dark',
      'grep' => 'f',
      'ticketlink' => 'yes',
    );
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using the defaults
      ->with($this->identicalTo($input_att))
      ->will($this->returnValue([]));
    $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = EventsList::shortCode($input_att);
    $this->assertNotEmpty($result);
  }

  /**
   * @param int $event_id (between 100 and 999!)
   * @param string $event_type a valid event type
   * 
   * @see \ytp_render_eventType
   */
  function createMockEvent($event_id = 2161, $event_type = "auftritt")
  {
    $e = new Event(true);
    $e->event_id = $event_id;
    $e->event_name = "my test event $event_id";
    $e->event_type = $event_type;
    $e->location_name = "My amazing stage $event_id";
    $e->location_city = "Paradise City $event_id";
    $e->event_datetime = "${event_id}-03-20 20:00:00";
    $e->yesticket_booking_url = "https://link-to-my-tickets/event-${event_id}";
    return $e;
  }
}
