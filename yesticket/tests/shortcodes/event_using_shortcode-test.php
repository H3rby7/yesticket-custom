<?php

use \YesTicket\EventUsingShortcode;

include_once(__DIR__ . "/../utility.php");

class EventUsingShortcodeTest extends WP_UnitTestCase
{

  private $impl = null;

  public function set_up(): void
  {
    parent::set_up();
    \add_filter('locale', function () {
      return 'en_EN';
    }, 69);
    $this->impl = $this->getMockBuilder(EventUsingShortcode::class)
      ->disableOriginalConstructor()
      ->setMethods(['render_contents', 'getInstance'])
      ->getMock();
  }

  public function tear_down(): void
  {
    parent::tear_down();
    \remove_all_filters('locale', 69);
  }

  function run_render_eventType($expected, $input)
  {
    ob_start();
    $this->impl->render_eventType($input);
    $this->assertSame($expected, ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers YesTicket\EventUsingShortcode::render_eventType
   */
  function test_render_eventType_auftritt_lc()
  {
    ob_start();
    $this->impl->render_eventType("auftritt");
    $this->assertSame("Performance", ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers YesTicket\EventUsingShortcode::render_eventType
   */
  function test_render_eventType_auftritt_uc()
  {
    ob_start();
    $this->impl->render_eventType("Auftritt");
    $this->assertSame("Performance", ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers YesTicket\EventUsingShortcode::render_eventType
   */
  function test_render_eventType_workshop_lc()
  {
    ob_start();
    $this->impl->render_eventType("workshop");
    $this->assertSame("Workshop", ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers YesTicket\EventUsingShortcode::render_eventType
   */
  function test_render_eventType_workshop_uc()
  {
    ob_start();
    $this->impl->render_eventType("Workshop");
    $this->assertSame("Workshop", ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers YesTicket\EventUsingShortcode::render_eventType
   */
  function test_render_eventType_festival_lc()
  {
    ob_start();
    $this->impl->render_eventType("festival");
    $this->assertSame("Festival", ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers YesTicket\EventUsingShortcode::render_eventType
   */
  function test_render_eventType_festival_uc()
  {
    ob_start();
    $this->impl->render_eventType("Festival");
    $this->assertSame("Festival", ob_get_clean(), "should be translated to 'EN'");
  }

  /**
   * @covers YesTicket\EventUsingShortcode::render_eventType
   */
  function test_render_eventType_unknownEventType()
  {
    ob_start();
    $this->impl->render_eventType("unknownEventType");
    $this->assertSame("unknownEventType", ob_get_clean(), "should return input as this type is unknown to us.");
  }

}
