<?php

namespace YesTicket;

include_once(__DIR__ . "/../../utility.php");

class TemplateSettingsRequiredTest extends \YTP_TemplateTestCase
{
  /**
   * provide to use in templating
   */
  function get_slug()
  {
    return 'test-TemplateSettingsRequired';
  }

  /**
   * As we do not register any options we cannot test them in this templating. 
   * We can only test the wordpress wrapping and fields.
   */
  function test_html()
  {
    $slug = $this->get_slug();
    $action = "http://127.0.0.1/wp-admin/options.php?test=$slug";
    $request_url = "/wp-admin/admin.php?page=yesticket-settings";
    $this->expectTranslate("Save Changes", "default");
    $this->includeTemplate(__FILE__, \compact("action", "request_url"));
  }
}
