<?php

include_once(__DIR__ . "/../yesticket_helpers.php");
include_once("examples_page.php");
include_once("settings_page.php");

class YesTicketPluginMenu
{
  /**
   * Get the capability required to view the admin page.
   *
   * @return string
   */
  public function get_capability()
  {
    return 'install_plugins';
  }

  /**
   * Get the title of the admin page in the WordPress admin menu.
   *
   * @return string
   */
  public function get_menu_title()
  {
    return 'Yesticket';
  }

  /**
   * Get the title of the admin page.
   *
   * @return string
   */
  public function get_page_title()
  {
    return 'YesTicket';
  }

  /**
   * Get the slug used by the admin page.
   *
   * @return string
   */
  public function get_slug()
  {
    return 'yesticket';
  }

  public function get_styles()
  {
    wp_enqueue_style($this->get_slug(), plugins_url('styles.css', __FILE__), false, 'all');
  }
}

function ytp_add_plugin_menu()
{
  $template_dir = __DIR__ . '/templates';
  $admin_page = new YesTicketPluginMenu();
  $examples_page = new YesTicketExamples($admin_page->get_slug(), $template_dir);
  $settings_page = new YesTicketSettings($admin_page->get_slug(), $template_dir);
  $admin_page_slug = $admin_page->get_slug();

  add_action('admin_enqueue_scripts', array($admin_page, 'get_styles'));

  if (!YesTicketPluginOptions::getInstance()->areNecessarySettingsSet()) {
    $admin_page_slug = $settings_page->get_slug();
  }

  add_menu_page(
    $admin_page->get_page_title(),
    $admin_page->get_menu_title(),
    $admin_page->get_capability(),
    $admin_page_slug,
    '',
    ytp_getImageUrl('YesTicket_icon_small.png')
  );

  if (YesTicketPluginOptions::getInstance()->areNecessarySettingsSet()) {
    add_submenu_page(
      $admin_page->get_slug(),
      $examples_page->get_page_title(),
      $examples_page->get_menu_title(),
      $examples_page->get_capability(),
      $admin_page->get_slug(),
      array($examples_page, 'render_page')
    );
  }

  add_submenu_page(
    $admin_page->get_slug(),
    $settings_page->get_page_title(),
    $settings_page->get_menu_title(),
    $settings_page->get_capability(),
    $settings_page->get_slug(),
    array($settings_page, 'render_page')
  );

  add_action('admin_init', [$settings_page, 'configure']);
}
add_action('admin_menu', 'ytp_add_plugin_menu');
