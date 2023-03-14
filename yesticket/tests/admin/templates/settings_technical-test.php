<?php

namespace YesTicket;

include_once(__DIR__ . "/../../utility.php");

class TemplateSettingsTechnicalTest extends \YTP_TemplateTestCase
{
  /**
   * provide to use in templating
   */
  function get_slug()
  {
    return 'test-TemplateSettingsTechnical';
  }
  /**
   * provide to use in templating
   */
  function get_parent_slug()
  {
    return 'test-TemplateSettingsTechnical-parent';
  }

  function test_renders_valid_localized_html()
  {
    $slug = $this->get_slug();
    $action = "http://127.0.0.1/wp-admin/options.php?test='$slug'";
    $this->expectTranslate("Save Changes", "default");
    $this->expectTranslate("If your changes in YesTicket are not reflected fast enough, try to: ");
    $this->expectTranslate("Clear Cache");
    $this->includeTemplate(__FILE__, \compact("action"));
  }

}
