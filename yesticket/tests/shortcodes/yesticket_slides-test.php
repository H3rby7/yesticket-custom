<?php

use \YesTicket\Api;
use \YesTicket\Slides;
use \YesTicket\Model\Event;

include_once(__DIR__ . "/../utility.php");

class SlidesShortcodeTest extends YTP_TranslateTestCase
{

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Slides"));
  }

  /**
   * Initiate Mock for @see Api
   */
  private function initMock()
  {
    // Inject Mock into API::$instance
    $_cache_property = new ReflectionProperty(Slides::class, "api");
    $_cache_property->setAccessible(true);
    $instance = Slides::getInstance();
    $api_mock = $this->getMockBuilder(Api::class)
      ->setMethods(['getEvents'])
      ->getMock();
    $_cache_property->setValue($instance, $api_mock);
    return $api_mock;
  }

  /**
   * @covers YesTicket\Slides
   * @covers YesTicket\EventUsingShortcode
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(Slides::getInstance());
  }

  /**
   * @covers YesTicket\Slides::registerFiles
   */
  function todo_test_shortcode_registerFiles()
  {
    // TODO: test if styles and js get registered!
  }

  /**
   * @covers YesTicket\Slides::shortCode
   */
  function todo_test_shortcode_enqueues_files()
  {
    // TODO: test if styles and js get enqueued!
  }

  /**
   * @covers YesTicket\Slides::print_eventDescription
   */
  function test_print_eventDescription()
  {
    $this->run_print_eventDescription(
      'some description',
      'some description',
      500,
      "Should not crop, because length is longer than text"
    );
    $this->run_print_eventDescription(
      'some description that is longer than the threshold and has n[...]',
      'some description that is longer than the threshold and has no punctuation before the threshold',
      60,
      "Should crop with '[...]', because no punctuation mark before threshold"
    );
    $this->run_print_eventDescription(
      'some description that is longer than the threshold.',
      'some description that is longer than the threshold. However this one has a point so it gets cropped there!',
      60,
      "Should crop at the 'dot'"
    );
    $this->run_print_eventDescription(
      'A long description! One with a point. Or rather a question?',
      'A long description! One with a point. Or rather a question? Well will see what it does!',
      60,
      "Should crop at the 'questionmark'"
    );
    $this->run_print_eventDescription(
      'A long description! One with a point.',
      'A long description! One with a point. Or rather a question? Well will see what it does!',
      50,
      "Should crop at the 'dot'"
    );
    $this->run_print_eventDescription(
      'A long description!',
      'A long description! One with a point. Or rather a question? Well will see what it does!',
      30,
      "Should crop at the first 'exclamationmark'"
    );
    $this->run_print_eventDescription(
      'A long description! One with a point. Or rather a question? Well will see what it does!',
      'A long description! One with a point. Or rather a question? Well will see what it does!',
      87,
      "Should not crop."
    );
  }

  function run_print_eventDescription($expected, $description, $length, $msg = '')
  {
    $s = Slides::getInstance();
    \ob_start();
    $s->print_eventDescription(json_decode('{"event_description":"'.$description.'"}'), array("teaser-length" => $length));
    $this->assertSame($expected, \ob_get_clean(), $msg);
  }

  function test_shortcode_no_events()
  {
    // Mock API
    $this->initMock();
    // Translations
    $this->expectTranslate("improv theatre show");
    $this->expectTranslate("welcome to our");
    $this->expectTranslate("where everything is made up");
    $expectedContent = $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = Slides::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-slides', $asXML);
  }

  function test_shortcode_exception()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      ->with($this->anything())
      ->will($this->throwException(new InvalidArgumentException("api-key not set!")));
    // Translations
    $this->expectTranslate("improv theatre show");
    $this->expectTranslate("welcome to our");
    $this->expectTranslate("where everything is made up");
    // Call shortcode
    $result = Slides::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString("api-key not set!", $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-slides', $asXML);
  }

  function test_shortcode_message_no_items_found()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getEvents')
      ->with($this->anything())
      ->will($this->returnValue(json_decode('{"message":"no items found"}')));
    // Translations
    $this->expectTranslate("improv theatre show");
    $this->expectTranslate("welcome to our");
    $this->expectTranslate("where everything is made up");
    $expectedContent = $this->expectTranslate("At this time no upcoming events are available.");
    // Call shortcode
    $result = Slides::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-slides', $asXML);
  }

  /**
   * Run using defaults $att
   */
  function test_shortcode_defaults()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockEvent()];
    $expectedWelcome1 = $this->expectTranslate("welcome to our");
    $expectedWelcome2 = $this->expectTranslate("improv theatre show");
    $expectedWelcome3 = $this->expectTranslate("where everything is made up");
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using the defaults
      ->with($this->identicalTo(array('type' => 'all', 'env' => 'prod', 'count' => 100, 'grep' => NULL, 'teaser-length' => '250', 'ms-per-slide' => '10000', 'text-scale' => '100%', 'color-1' => 'white', 'color-2' => 'black', 'welcome-1' => $expectedWelcome1, 'welcome-2' => $expectedWelcome2, 'welcome-3' => $expectedWelcome3)))
      ->will($this->returnValue($mock_result));
    // Translations
    $this->expectTranslate("F j, Y \a\\t g:i A");
    // Call shortcode
    $result = Slides::shortCode([]);
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-slides', $asXML);
    $this->assertStyle($asXML, 'white', 'black', '100%');
    $this->assertJs($asXML, 10000);
    // ytp-slides container
    $container = $asXML->xpath("//*[@id='ytp-slides']")[0];
    $this->assertNotEmpty($container, "Should have an element with id 'ytp-slides'");
    // article
    $article = $container->xpath("//article[@id='webslides']")[0];
    $this->assertNotEmpty($article, "Should have an <article> with id 'webslides'");
    $sections = $article->xpath("./section");
    $this->assertCount(\count($sections), $article->xpath("./*"), "Article should only have elements of type <section> as children");
    $this->assertWelcomeSlide($sections[0], $expectedWelcome1, $expectedWelcome2, $expectedWelcome3);
    // mock-Event section
    $eventSection = $sections[1];
    $this->assertHtmlContainsText("my test event 2161", $eventSection, "Slide should contain event_name");
    $this->assertHtmlContainsText("March 20, 2161 at 8:00 PM", $eventSection, "Slide should contain date and time");
    $this->assertHtmlContainsText("My amazing stage 2161", $eventSection, "Slide should contain location_name");
    $this->assertHtmlContainsText("This is the event description of event 2161", $eventSection, "Slide should contain event description");
    $this->assertHtmlContainsText("background-image:url('http://example.org/wp-json/yesticket/v1/picture/2161'", $eventSection, "Slide background should be the event image, taken via our WP API.");
  }

  /**
   * Test passing welcome-X texts and assert their presence in the first section (slide).
   */
  function test_shortcode_with_welcome_atts()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockEvent()];
    $welcome1 = "servus y'all to this";
    $welcome2 = "shooooooow";
    $welcome3 = "(we improvise)";
    $api_mock->expects($this->once())
      ->method('getEvents')
      // Expect call using different welcome texts
      ->with($this->identicalTo(array('type' => 'all', 'env' => 'prod', 'count' => 100, 'grep' => NULL, 'teaser-length' => '250', 'ms-per-slide' => '10000', 'text-scale' => '100%', 'color-1' => 'white', 'color-2' => 'black', 'welcome-1' => $welcome1, 'welcome-2' => $welcome2, 'welcome-3' => $welcome3)))
      ->will($this->returnValue($mock_result));
    // Translations (note that some of the translations are made to produce the shortcode default $att array.)
    $this->expectTranslate("F j, Y \a\\t g:i A");
    $this->expectTranslate("improv theatre show");
    $this->expectTranslate("welcome to our");
    $this->expectTranslate("where everything is made up");
    // Call shortcode
    $result = Slides::shortCode(array('welcome-1' => $welcome1, 'welcome-2' => $welcome2, 'welcome-3' => $welcome3));
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-slides', $asXML);
    $this->assertWelcomeSlide($asXML->xpath("//section")[0], $welcome1, $welcome2, $welcome3);
  }

  /**
   * Test passing new params in all $att values
   */
  function test_shortcode_set_all_att()
  {
    $input_att = array(
      'type' => 'performance',
      'env' => 'dev',
      'count' => 12,
      'grep' => 'f',
      'teaser-length' => '10',
      'ms-per-slide' => '777',
      'text-scale' => '15em',
      'color-1' => 'rgba(255,13,57,0.5)',
      'color-2' => '#33FF33',
      'welcome-1' => "servus y'all to this",
      'welcome-2' => 'shooooooow',
      'welcome-3' => '(we improvise)',
    );
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockEvent()];
    $api_mock->expects($this->once())
      ->method('getEvents')
      ->with($this->identicalTo($input_att))
      ->will($this->returnValue($mock_result));
    // Translations (note that some of the translations are made to produce the shortcode default $att array.)
    $this->expectTranslate("F j, Y \a\\t g:i A");
    $this->expectTranslate("improv theatre show");
    $this->expectTranslate("welcome to our");
    $this->expectTranslate("where everything is made up");
    // Call shortcode
    $result = Slides::shortCode($input_att);
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-slides', $asXML);
    $this->assertWelcomeSlide($asXML->xpath("//section")[0], $input_att["welcome-1"], $input_att["welcome-2"], $input_att["welcome-3"]);
    $this->assertStyle($asXML, 'rgba(255,13,57,0.5)', '#33FF33', '15em');
    $this->assertJs($asXML, 777);
    $eventSection = $asXML->xpath("//section")[1];
    $this->assertHtmlContainsText("This is th", $eventSection, "Start of event description should be visible");
    $this->assertHtmlDoesNotContainText("event description of event 2161", $eventSection, "Event description should be cropped!");
  }

  /**
   * Find <script> tag in $xml and assert contents
   * 
   * @param SimpleXMLElement $xml
   * @param string $colorPrimary expected css color value
   * @param string $colorContrast expected css color value
   * @param string $fontSize expected css font size
   */
  function assertStyle($xml, $colorPrimary, $colorContrast, $fontSize)
  {
    $styles = $xml->xpath("//style")[0];
    $this->assertHtmlContainsText("--ytp--color--primary: $colorPrimary", $styles);
    $this->assertHtmlContainsText("--ytp--color--contrast: $colorContrast", $styles);
    $this->assertHtmlContainsText("font-size: $fontSize", $styles);
  }

  /**
   * Find <script> tag in $xml and assert contents
   * 
   * @param SimpleXMLElement $xml
   * @param number $slideTime expected as config arg to Webslides
   */
  function assertJs($xml, $slideTime)
  {
    $script = $xml->xpath("//script")[0];
    $this->assertHtmlContainsText("window.addEventListener('load'", $script);
    $this->assertHtmlContainsText("new WebSlides(", $script);
    $this->assertHtmlContainsText("autoslide: $slideTime", $script);
  }

  /**
   * Assert text values in first slide
   * 
   * @param SimpleXMLElement $firstSlide the first slide's <section>
   * @param string $welcome1 expected text
   * @param string $welcome2 expected text
   * @param string $welcome3 expected text
   */
  function assertWelcomeSlide($firstSlide, $welcome1, $welcome2, $welcome3)
  {
    $this->assertHtmlContainsText($welcome1, $firstSlide, "First slide should contain '$welcome1'");
    $this->assertHtmlContainsText($welcome2, $firstSlide, "First slide should contain '$welcome2'");
    $this->assertHtmlContainsText($welcome3, $firstSlide, "First slide should contain '$welcome3'");
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
    return $e;
  }
}
