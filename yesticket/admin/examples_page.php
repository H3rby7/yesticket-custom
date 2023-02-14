<?php

include_once(__DIR__ . "/../yesticket_helpers.php");

class YesTicketExamples
{
  /**
   * Path to the example templates.
   *
   * @var string
   */
  private $template_path;

  /**
   * Slug of the parent menu entry
   *
   * @var string
   */
  private $parent_slug;

  /**
   * Constructor.
   *
   * @param string $template_path
   */
  public function __construct($parent_slug, $template_path)
  {
    $this->parent_slug = $parent_slug;
    $this->template_path = rtrim($template_path, '/');
  }

  /**
   * Get the capability required to view the examples page.
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
    return __('Shortcodes', 'yesticket');
  }

  /**
   * Get the title of the admin page.
   *
   * @return string
   */
  public function get_page_title()
  {
    return __('YesTicket Plugin Shortcodes', 'yesticket');
  }

  /**
   * Get the parent slug of the admin page.
   *
   * @return string
   */
  public function get_parent_slug()
  {
    return $this->parent_slug;
  }

  /**
   * Get the slug used by the admin page.
   *
   * @return string
   */
  public function get_slug()
  {
    return $this->parent_slug . '-examples';
  }

  /**
   * Render the example page.
   */
  public function render_page()
  {
    $this->render_template('header');
    $this->render_template('examples_wrapper');
  }

  /**
   * Renders the given template if it's readable.
   *
   * @param string $template
   */
  function render_template($template, $variables = array())
  {
    $template_path = $this->template_path . '/' . $template . '.php';

    if (!is_readable($template_path)) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": 'Template not found: $template_path'");
      return;
    }
    // Extract the variables to a local namespace
    extract($variables);

    include $template_path;
  }

  private function ytp_render_shortcode_preview($shortcode, $previewImageFileName)
  {
    $image_url = ytp_getImageUrl($previewImageFileName);
    $alt_text = sprintf(
      /* translators: %s is replaced with the shortcode, e.G. 'yesticket_events' */
      __('[%s] preview', "yesticket"),
      $shortcode
    );
    return <<<EOD
          <div class="show_on_hover ytp-admin-shortcode-preview">
            <img src="$image_url" alt="$alt_text">
          </div>
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  public function render_navigation_tab($tab, $activeTab, $tabName, $shortcode, $image)
  {
    $page = $this->get_parent_slug();
    $preview = $this->ytp_render_shortcode_preview($shortcode, $image);
    $classIfActive = "";
    if ($activeTab == $tab) {
      $classIfActive = "nav-tab-active";
    }
    if (!empty($tab)) {
      $tab = "&tab=" . $tab;
    }
    print <<<EOD
          <a href="?page=$page$tab" 
             class="hover_trigger nav-tab $classIfActive">$tabName</a>
          $preview
EOD;
    // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented and followed by newline !!!!
  }

  public function render_tabContent($activeTab)
  {
    switch ($activeTab):
      case 'list':
        $this->render_template('examples_events_list');
        break;
      case 'cards':
        $this->render_template('examples_events_cards');
        break;
      case 'testimonials':
        $this->render_template('examples_testimonials');
        break;
      default:
        $this->render_template('examples_events');
    endswitch;
  }
}
