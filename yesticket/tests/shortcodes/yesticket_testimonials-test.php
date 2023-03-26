<?php

use \YesTicket\Api;
use \YesTicket\Testimonials;
use \YesTicket\Model\Event;

include_once(__DIR__ . "/../utility.php");

class TestimonialsShortcodeTest extends YTP_TranslateTestCase
{

  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Testimonials"));
  }

  /**
   * Initiate Mock for @see Api
   */
  private function initMock()
  {
    // Inject Mock into API::$instance
    $_cache_property = new ReflectionProperty(Testimonials::class, "api");
    $_cache_property->setAccessible(true);
    $instance = Testimonials::getInstance();
    $api_mock = $this->getMockBuilder(Api::class)
      ->setMethods(['getTestimonials'])
      ->getMock();
    $_cache_property->setValue($instance, $api_mock);
    return $api_mock;
  }

  /**
   * @covers YesTicket\Testimonials
   * @covers YesTicket\EventUsingShortcode
   */
  function test_get_instance()
  {
    $this->assertNotEmpty(Testimonials::getInstance());
  }

  function test_shortcode_no_Testimonials()
  {
    // Mock API
    $this->initMock();
    // Translations
    $expectedContent = $this->expectTranslate("At this time no audience feedback is present.");
    // Call shortcode
    $result = Testimonials::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-testimonials', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-jump', $asXML);
  }

  function test_shortcode_exception()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getTestimonials')
      // Expect call using the defaults
      ->with($this->anything())
      ->will($this->throwException(new InvalidArgumentException("api-key not set!")));
    // Call shortcode
    $result = Testimonials::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString("api-key not set!", $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-testimonials', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-jump', $asXML);
  }

  function test_shortcode_message_no_items_found()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getTestimonials')
      // Expect call using the defaults
      ->with($this->anything())
      ->will($this->returnValue(json_decode('{"message":"no items found"}')));
    // Translations
    $expectedContent = $this->expectTranslate("At this time no audience feedback is present.");
    // Call shortcode
    $result = Testimonials::shortCode([]);
    $this->assertNotEmpty($result);
    $this->assertStringContainsString($expectedContent, $result, "Should contain the 'error' message.");
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-testimonials', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-jump', $asXML);
  }

  function test_shortcode_defaults()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockTestimonial()];
    $api_mock->expects($this->once())
      ->method('getTestimonials')
      // Expect call using the defaults
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'all', 'count' => 100, 'design' => 'basic', 'details' => 'no',)))
      ->will($this->returnValue($mock_result));
    // Translations
    $this->expectTranslate("F j, Y");
    $this->expectTranslate('%1$s on %2$s.');
    // Call shortcode
    $result = Testimonials::shortCode([]);
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-testimonials', $asXML);
    $this->assertHtmlDoesNotHaveClass('ytp-jump', $asXML);
    // Check on our item.
    $item = $asXML->xpath("div")[0];
    $this->assertNotEmpty($item, "Should contain an item.");
    $this->assertHtmlHasClass('ytp-testimonial-row', $item);
    // General information
    $this->assertHtmlContainsText("March 20, 2161", $item, "Item should show the date and time.");
    $this->assertHtmlContainsText("Person who has been to 2161", $item, "Item should show the author.");
    // Assert no details are shown
    $this->assertHtmlDoesNotContainText("Name of E=2161", $item, "Item should not show event_name, because \$att details is 'no'");
  }

  function test_shortcode_with_design_and_details()
  {
    // Mock API
    $api_mock = $this->initMock();
    $mock_result = [$this->createMockTestimonial()];
    $api_mock->expects($this->once())
      ->method('getTestimonials')
      // Expect call using the defaults
      ->with($this->identicalTo(array('env' => NULL, 'api-version' => NULL, 'organizer' => NULL, 'key' => NULL, 'type' => 'all', 'count' => 100, 'design' => 'jump', 'details' => 'yes',)))
      ->will($this->returnValue($mock_result));
    // Translations
    $this->expectTranslate("F j, Y");
    $this->expectTranslate('%1$s on %2$s about \'%3$s\'.');
    // Call shortcode
    $result = Testimonials::shortCode(array('design' => 'jump', 'details' => 'yes',));
    $this->assertNotEmpty($result);
    // produces valid HTML
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-testimonials', $asXML);
    $this->assertHtmlHasClass('ytp-jump', $asXML);
    // Check on our item.
    $item = $asXML->xpath("div")[0];
    $this->assertNotEmpty($item, "Should contain an item.");
    $this->assertHtmlHasClass('ytp-testimonial-row', $item);
    // General information
    $this->assertHtmlContainsText("March 20, 2161", $item, "Item should show the date and time.");
    $this->assertHtmlContainsText("Person who has been to 2161", $item, "Item should show the author.");
    $this->assertHtmlContainsText("Name of E=2161", $item, "Item should not show event_name, because \$att details is 'yes'");
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
      'design' => 'jump', 
      'details' => 'yes',
    );
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getTestimonials')
      // Expect call using the defaults
      ->with($this->identicalTo($input_att))
      ->will($this->returnValue([]));
    $this->expectTranslate("At this time no audience feedback is present.");
    // Call shortcode
    $result = Testimonials::shortCode($input_att);
    $this->assertNotEmpty($result);
  }
  
  function test_shortcode_set_unsupported_design()
  {
    // Mock API
    $api_mock = $this->initMock();
    $api_mock->expects($this->once())
      ->method('getTestimonials')
      // Expect call using the defaults
      ->with($this->anything())
      ->will($this->returnValue([]));
    $this->expectTranslate("At this time no audience feedback is present.");
    // Call shortcode
    $result = Testimonials::shortCode(array('design' => 'invalid-design'));
    $this->assertNotEmpty($result);
    $asXML = $this->validateAndGetAsXml($result);
    $this->assertHtmlHasClass('ytp-testimonials', $asXML);
    $this->assertHtmlHasClass('design-must-be-basic-or-jump', $asXML);
  }

  /**
   * @param int $event_id (between 1000 and 9999!)
   * 
   * @see \ytp_render_eventType
   */
  function createMockTestimonial($event_id = 2161)
  {
    return json_decode('{"event_name": "Name of E=' . $event_id . '", "text":"I have been to event ' . $event_id . '", "source": "Person who has been to ' . $event_id . '", "date": "'. $event_id. '-03-20 20:00:00"}');
  }
}
