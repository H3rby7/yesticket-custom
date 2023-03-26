<?php

use \YesTicket\Api;
use \YesTicket\EventsCards;
use \YesTicket\Model\Event;

include_once(__DIR__ . "/../utility.php");

class EventsCardsShortcodeTest extends YTP_TranslateTestCase
{

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\EventsCards"));
  }

  /**
   * Initiate Mock for @see Api
   */
  private function initMock()
  {
    // Inject Mock into API::$instance
    $_cache_property = new ReflectionProperty(EventsCards::class, "api");
    $_cache_property->setAccessible(true);
    $instance = EventsCards::getInstance();
    $api_mock = $this->getMockBuilder(Api::class)
      ->setMethods(['getEvents'])
      ->getMock();
    $_cache_property->setValue($instance, $api_mock);
    return $api_mock;
  }

  /**
   * @covers YesTicket\EventsCards
   * @covers YesTicket\EventUsingShortcode
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(EventsCards::getInstance());
  }

  function test_shortcode_no_events()
  {
    // Mock API
    $this->initMock();
    // Translations
    $expectedContent = $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = EventsCards::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-cards', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
  }

  function test_shortcode_exception()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      ->with($this->anything())
      ->will($this->throwException(new InvalidArgumentException("api-key not set!")));
    // Call shortcode
    $result = EventsCards::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString("api-key not set!", $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-cards', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
  }

  function test_shortcode_message_no_cards_found()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      ->with($this->anything())
      ->will($this->returnValue(json_decode('{"message":"no cards found"}')));
    // Translations
    $expectedContent = $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = EventsCards::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-cards', $asXML);
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
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'all', 'count' => 9, 'theme' => 'light', 'grep' => NULL)))
      ->will($this->returnValue($mock_result));
    // Call shortcode
    $result = EventsCards::shortCode([]);
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-cards', $asXML);
    $this->assertHtmlHasClass('ytp-light', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-dark', $asXML);
    // Check on our card.
    $card = $asXML->xpath("a[@href]")[0];
    $this->assertNotEmpty($card, "Should contain an card.");
    $this->assertNotEmpty('ytp-event-card', $card->xpath("[@class='ytp-event-card']"));
    // General information
    $this->assertHtmlContainsText("Mar", $card, "Card should show the month");
    $this->assertHtmlContainsText("20", $card, "Card should show the day");
    $this->assertHtmlContainsText("2161", $card, "Card should show the year");
    $this->assertHtmlContainsText("my test event 2161", $card, "card should show the event_name.");
    $this->assertHtmlContainsText("My amazing stage 2161", $card, "card should show the location_name.");
  }

  function test_shortcode_with_type_theme_and_count()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockEvent()];
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using different type, theme and count
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'performance', 'count' => 16, 'theme' => 'dark', 'grep' => NULL)))
      ->will($this->returnValue($mock_result));
    // Call shortcode
    $result = EventsCards::shortCode(array('type' => 'performance', 'theme' => 'dark', 'count' => 16,));
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-event-cards', $asXML);
    $this->assertHtmlHasClass('ytp-dark', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-light', $asXML);
    // Check on our card.
    $card = $asXML->xpath("a[@href]")[0];
    $this->assertNotEmpty($card, "Should contain an card.");
    $this->assertNotEmpty('ytp-event-card', $card->xpath("[@class='ytp-event-card']"));
    // General information
    $this->assertHtmlContainsText("Mar", $card, "Card should show the month");
    $this->assertHtmlContainsText("20", $card, "Card should show the day");
    $this->assertHtmlContainsText("2161", $card, "Card should show the year");
    $this->assertHtmlContainsText("my test event 2161", $card, "card should show the event_name.");
    $this->assertHtmlContainsText("My amazing stage 2161", $card, "card should show the location_name.");
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
    );
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      ->with($this->identicalTo($input_att))
      ->will($this->returnValue([]));
    $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = EventsCards::shortCode($input_att);
    $this->assertNotEmpty($result);
  }

  /**
   * @param int $event_id (between 1000 and 9999!)
   * @param string $event_type a valid event type
   * 
   * @see \YesTicket\EventUsingShortcode::render_eventType
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
