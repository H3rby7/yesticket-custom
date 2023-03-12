<?php


class FunctionsTest extends \WP_UnitTestCase
{

  /**
   * @covers ::\ytp_init_callback
   */
  function test_ytp_init_callback_exists()
  {
    $this->assertTrue(function_exists("\ytp_init_callback"));
  }
  /**
   * @covers ::\ytp_init_callback
   */
  function test_ytp_init_check_registered_styles()
  {
    \ytp_init_callback();
    $this->assertTrue(
      \wp_style_is('yesticket', 'registered'),
      "Should have registered 'yesticket' style."
    );
    $this->assertTrue(
      \wp_style_is('yesticket-admin', 'registered'),
      "Should have registered 'yesticket-admin' style."
    );
    // sadly checking for the plugin textdomain seems almost impossible :/
    // $this->assertTrue(
    //   \is_textdomain_loaded('yesticket'),
    //   "Textdomain 'yesticket' should have been loaded."
    // );
  }
}
