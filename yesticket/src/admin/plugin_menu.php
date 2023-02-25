<?php

namespace YesTicket\Admin;
use YesTicket\PluginOptions;

include_once("examples_page.php");
include_once("settings_page.php");
include_once(__DIR__ . "/../helpers/functions.php");
include_once(__DIR__ . "/../helpers/plugin_options.php");

/**
 * Builds the menu entries for the YesTicket plugin
 */
class PluginMenu
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
   * Get the title of the admin page (displayed as browser tab name)
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

}

/**
 * Register the menu pages using the Settings API.
 */
function add_plugin_menu()
{
  $admin_page = new PluginMenu();
  $examples_page = new Examples($admin_page->get_slug());
  $settings_page = new SettingsPage($admin_page->get_slug());
  $admin_page_slug = $admin_page->get_slug();

  if (!PluginOptions::getInstance()->areNecessarySettingsSet()) {
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

  if (PluginOptions::getInstance()->areNecessarySettingsSet()) {
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

add_action('admin_menu', 'YesTicket\Admin\add_plugin_menu');
