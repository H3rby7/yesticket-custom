<?php

use \YesTicket\Api;
use \YesTicket\Events;
use \YesTicket\Model\Event;

include_once(__DIR__ . "/../utility.php");

class EventsShortcodeTest extends YTP_TranslateTestCase
{

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Events"));
  }

  /**
   * Initiate Mock for @see Api
   */
  private function initMock()
  {
    // Inject Mock into API::$instance
    $_cache_property = new ReflectionProperty(Events::class, "api");
    $_cache_property->setAccessible(true);
    $instance = Events::getInstance();
    $api_mock = $this->getMockBuilder(Api::class)
      ->setMethods(['getEvents'])
      ->getMock();
    $_cache_property->setValue($instance, $api_mock);
    return $api_mock;
  }

  /**
   * @covers YesTicket\Events
   * @covers YesTicket\EventUsingShortcode
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(Events::getInstance());
  }

  function test_shortcode_no_events()
  {
    // Mock API
    $this->initMock();
    // Translations
    $expectedContent = $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = Events::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-events', $asXML);
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
    $result = Events::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString("api-key not set!", $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-events', $asXML);
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
    $result = Events::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-events', $asXML);
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
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'all', 'count' => 100, 'theme' => 'light', 'grep' => NULL, 'details' => 'no',)))
      ->will($this->returnValue($mock_result));
    // Translations
    $expectedType = $this->expectTranslate("Performance");
    $this->expectTranslate("F j, Y \a\\t g:i A");
    $this->expectTranslate("Tickets");
    // Call shortcode
    $result = Events::shortCode([]);
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-events', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-dark', $asXML);
    // Check on our item.
    $item = $asXML->xpath("div[@id='ytp-event-2161']")[0];
    $this->assertNotEmpty($item, "Should contain an item.");
    $this->assertHtmlHasClass('ytp-event-row', $item);
    // General information
    $this->assertHtmlContainsText($expectedType, $item, "Item should display its type, because \$att type is 'all'.");
    $this->assertHtmlContainsText("March 20, 2161 at 8:00 PM", $item, "Item should show the date and time.");
    $this->assertHtmlContainsText("my test event 2161", $item, "Item should show the event_name.");
    $this->assertHtmlContainsText("My amazing stage 2161", $item, "Item should show the location_name.");
    $this->assertHtmlContainsText("Paradise City 2161", $item, "Item should show the location_city.");
    // Assert no details are shown
    $this->assertHtmlDoesNotContainText("This is the event description of event 2161", $item, "Item should not show details, because \$att details is 'no'");
    $this->assertHtmlDoesNotContainText("This is the event description of event 2161", $item, "Item should not show details, because \$att details is 'no'");
    $this->assertHtmlDoesNotContainText("This offers help for event 2161", $item, "Item should not show details, because \$att details is 'no'");
    $this->assertHtmlDoesNotContainText("Stringified ticket sction for 2161", $item, "Item should not show details, because \$att details is 'no'");
  }

  function test_shortcode_with_type_theme_and_details()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockEvent()];
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using the defaults
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'performance', 'count' => 100, 'theme' => 'dark', 'grep' => NULL, 'details' => 'yes',)))
      ->will($this->returnValue($mock_result));
    // Translations
    $this->expectTranslate("F j, Y \a\\t g:i A");
    $this->expectTranslate("Tickets");
    $this->expectTranslate("Show details");
    $this->expectTranslate("Back to top");
    $this->expectTranslate("Hints");
    $this->expectTranslate("Location");
    $this->expectTranslate("Tickets");
    // Call shortcode
    $result = Events::shortCode(array('type' => 'performance', 'theme' => 'dark', 'details' => 'yes',));
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-events', $asXML);
    $this->assertHtmlHasClass('ytp-dark', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-light', $asXML);
    // Check on our item.
    $item = $asXML->xpath("div[@id='ytp-event-2161']")[0];
    $this->assertNotEmpty($item, "Should contain an item.");
    // General information
    $this->assertHtmlDoesNotContainText("Performance", $item, "Item should NOT display its type, because \$att type is 'performance'.");
    $this->assertHtmlHasClass('ytp-event-row', $item);
    $this->assertHtmlContainsText("March 20, 2161 at 8:00 PM", $item, "Item should show the date and time.");
    $this->assertHtmlContainsText("my test event 2161", $item, "Item should show the event_name.");
    $this->assertHtmlContainsText("My amazing stage 2161", $item, "Item should show the location_name.");
    $this->assertHtmlContainsText("Paradise City 2161", $item, "Item should show the location_city.");
    // Assert no details are shown
    $this->assertHtmlContainsText("This is the event description of event 2161", $item, "Item should provide details, as \$att details is 'yes'");
    $this->assertHtmlContainsText("This is the event description of event 2161", $item, "Item should provide details, as \$att details is 'yes'");
    $this->assertHtmlContainsText("This offers help for event 2161", $item, "Item should provide details, as \$att details is 'yes'");
    $this->assertHtmlContainsText("Stringified ticket sction for 2161", $item, "Item should provide details, as \$att details is 'yes'");
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
      'details' => 'yes',
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
    $result = Events::shortCode($input_att);
    $this->assertNotEmpty($result);
  }

  /**
   * @param int $event_id (between 1000 and 9999!)
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
    $e->event_description = "This is the event description of event ${event_id}";
    $e->event_notes_help = "This offers help for event ${event_id}";
    $e->tickets = "Stringified ticket sction for ${event_id}";
    return $e;
  }
}
