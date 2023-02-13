<?php

include_once(__DIR__ . "/../yesticket_helpers.php");
include_once("settings_base.php");
include_once("settings_required.php");
include_once("settings_technical.php");

class YesTicketSettings extends YesTicketSettingsBase
{

  protected $required;
  protected $technical;

  /**
   * Configure the admin page using the Settings API.
   */
  public function configure()
  {
    $this->required = new YesTicketSettingsRequired($this->get_slug(), $this->template_path);
    $this->technical = new YesTicketSettingsTechnical($this->get_slug(), $this->template_path);
  }

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
    return __("Settings");
  }

  /**
   * Get the title of the admin page.
   *
   * @return string
   */
  public function get_page_title()
  {
    return 'YesTicket ' . __("Settings");
  }

  /**
   * Render the settings page.
   */
  public function render_page()
  {
    $this->render_template('header');
    $this->render_template('settings');
  }

  function feedback()
  {
    echo $this->required->feedback();
    echo $this->technical->feedback();
  }

  public function render_navigation_tab($tab, $activeTab, $tabName)
  {
    $page = $this->get_slug();
    $classIfActive = "";
    if ($activeTab == $tab) {
      $classIfActive = "nav-tab-active";
    }
    if (!empty($tab)) {
      $tab = "&tab=" . $tab;
    }
    print "<a href='?page=$page$tab' class='hover_trigger nav-tab $classIfActive'>$tabName</a>";
  }

  public function render_tabContent($activeTab)
  {
    switch ($activeTab):
      case 'technical':
        $this->technical->render();
        break;
      default:
        $this->required->render();
    endswitch;
  }
}
