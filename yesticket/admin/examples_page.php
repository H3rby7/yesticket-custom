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
    return 'Shortcodes';
  }

  /**
   * Get the title of the admin page.
   *
   * @return string
   */
  public function get_page_title()
  {
    return 'YesTicket Plugin Shortcodes';
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
  private function render_template($template, $variables = array())
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
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  public function render_navigation_tab($tab, $activeTab, $tabName, $shortcode, $image)
  {
    $page = $this->get_parent_slug();
    $preview = "";
    if (isset($shortcode) and isset($image)) {
      $preview = $this->ytp_render_shortcode_preview($shortcode, $image);
    }
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
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
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

  // $type could be "Events" or "Testimonials"
  function render_optionType($type)
  { ?>
    <h4>Type</h4>
    <?php if ($type === 'events') { ?>
      <p><?php echo __("Using <b>type</b> you can filter your events by type.", "yesticket"); ?></p>
    <?php } elseif ($type === 'testimonials') { ?>
      <p><?php echo __("Using <b>type</b> you can filter your testimonials by type.", "yesticket"); ?></p>
    <?php } else {
      throw new AssertionError('Expect argument "$type" to be either "events" or "testimonials"!');
    } ?>
    <p class="ml-3">
      <span class="ytp-code">type="performance"</span>
      <?php
      /* translators: Explanation of using the shortcode option 'type="performance"'*/
      echo __("only shows/performances", "yesticket"); ?><br>
      <span class="ytp-code">type="workshop"</span>
      <?php
      /* translators: Explanation of using the shortcode option 'type="workshop"'*/
      echo __("only workshops", "yesticket"); ?><br>
      <span class="ytp-code">type="festivals"</span>
      <?php
      /* translators: Explanation of using the shortcode option 'type="festivals"'*/
      echo __("only festivals", "yesticket"); ?><br>
      <span class="ytp-code">type="all"</span>
      <?php
      /* translators: Explanation of using the shortcode option 'type="all"'*/
      echo __("Everything, mixed", "yesticket"); ?>
    </p>
  <?php
  }

  function render_optionTheme()
  { ?>
    <h4>Theme</h4>
    <p><?php echo __("Buttons will be in a light grey and match lighter backgrounds", "yesticket"); ?></p>
    <p class="ml-3"><span class="ytp-code">theme="light"</span> <?php echo __("Buttons will be in a light grey and match lighter backgrounds", "yesticket"); ?></p>
    <p class="ml-3"><span class="ytp-code">theme="dark"</span> <?php echo __("Buttons will be in a dark grey and match darker backgrounds", "yesticket"); ?></p>
    <p class="ml-3"><span class="ytp-code">theme=""</span> <?php echo __("If you do not provide a theme only basic formatting is applied. This is an option to provide your own clean CSS. Maybe you are a Webdesigner after all?", "yesticket"); ?></p>
  <?php
  }

  function render_optionCount()
  { ?>
    <h4>Count</h4>
    <p><?php echo __("Using <b>count</b> you can define the maximum amount of elements.", "yesticket"); ?></p>
    <p class="ml-3"><span class="ytp-code">count="6"</span>
      <?php
      /* translators: The sentence actually starts with a non-translatable codeblock 'count="6"'*/
      echo __("a maximum of 6 events is displayed", "yesticket"); ?></p>
    <p class="ml-3">
      <?php
      /* translators: Note, when using the shortcode option 'count'*/
      echo __("Please note, that count describes an upper limit. If fewer items are available, only these can be displayed.", "yesticket");
      ?></p>
<?php
  }
}
