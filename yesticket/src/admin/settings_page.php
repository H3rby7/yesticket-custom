<?php

include_once("settings_required.php");
include_once("settings_section.php");
include_once("settings_technical.php");
include_once(__DIR__ . "/../helpers/plugin_options.php");


/**
 * The admin page where settings can be adjusted
 */
class YesTicketSettingsPage extends YesTicketSettingsSection
{

  protected $required;
  protected $technical;

  /**
   * Configure the admin page using the Settings API.
   */
  public function configure()
  {
    $this->required = new YesTicketSettingsRequired($this->get_slug());
    $this->technical = new YesTicketSettingsTechnical($this->get_slug());
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
    return __("Yesticket Settings", "yesticket");
  }

  /**
   * Render the settings page.
   */
  public function render_page()
  {
    wp_enqueue_style('yesticket-admin');
    $this->render_template('header');
    if (YesTicketPluginOptions::getInstance()->areNecessarySettingsSet()) {
      $this->render_template('settings_wrapper');
    } else {
      $this->required->render();
    }
  }

  /**
   * Render feedback from setting actions.
   */
  function feedback()
  {
    echo $this->required->feedback();
    echo $this->technical->feedback();
  }

  /**
   * Prints the navigation tab for the given section
   * 
   * @param string $tab the tab id for this tab
   * @param string $activeTab the currently active tab
   * @param string $tabName the displayed name for this tab
   */
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

  /**
   * Prints the tab content of the active Tab
   * 
   * @param string $activeTab the currently active tab
   */
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
