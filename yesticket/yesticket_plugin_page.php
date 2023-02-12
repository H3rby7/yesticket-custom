<?php

add_action('admin_menu', array( 'YesTicketAdminPluginPage', 'action_getMenu' ));
add_action('admin_enqueue_scripts', array( 'YesTicketAdminPluginPage', 'action_getStyles' ));
add_action('admin_init', array( 'YesTicketAdminPluginPage', 'action_render_page'));

class YesTicketAdminPluginPage {
  static private $instance;
  static public function getInstance()
  {
      if (!isset(YesTicketAdminPluginPage::$instance)) {
        YesTicketAdminPluginPage::$instance = new YesTicketAdminPluginPage();
      }
      return YesTicketAdminPluginPage::$instance;
  }
  static public function action_getStyles()
  {
    wp_enqueue_style('yesticket', plugins_url('ytp-admin.css', __FILE__), false, 'all');
  }
  static public function action_getMenu()
  {
    add_menu_page('YesTicket', 'YesTicket', 'manage_options', 'yesticket-plugin', array('YesTicketAdminPluginPage', 'action_render_page'), ytp_getImageUrl('YesTicket_icon_small.png'));
  }
  static public function action_render_page() {
    YesTicketAdminPluginPage::getInstance()->render_page();
  }

  public function render_page()
{
  $activeTab = isset($_GET['tab']) ? $_GET['tab'] : null;
  $feedback = $this->ytp_admin_render_feedback();
  $logoUrl = ytp_getImageUrl('YesTicket_logo.png');
  /* translators: YesTicket Plugin Page Introduction Text*/
  $introduction_text = __("YesTicket is a ticketing system and we love wordpress - so here's our plugin! You can integrate upcoming events and audience feedback (testimonials) using shortcodes anywhere on your page. Be it pages, posts, widgets, ... get creative!", "yesticket");
  $tabContent = $this->render_tabContent($activeTab);
  $navigation = $this->render_nagivation($activeTab);
  $content = "";
  $content .= <<<EOD
    $feedback
    <h1><img src="$logoUrl" height="60" alt="YesTicket Logo"></h1>
    <p>$introduction_text</p>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!

    if (!$this->ytp_are_necessary_settings_set()) {
      $content .= $this->ytp_admin_render_settings($activeTab);
      return $content;
    }
    $shortcodes_heading = __("Shortcodes", "yesticket");
    $shortcodes_text = __("You can use multiple shortcodes on your page. For example you might start with a list of your shows, followed by your workshops and finish with testimonials of your audience.", "yesticket");
    /* translators: Hint text on plugin-page to preview different shortcodes */
    $preview_text = __("Hover above the tabs for a preview.", "yesticket");
    
    $content .= <<<EOD
      <h2>$shortcodes_heading</h2>
      <p>$shortcodes_text</p>
      <p>$preview_text</p>
      $navigation
      <div class="tab-content">
        $tabContent
      </div>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
    return $content;
  }

  private function render_tabContent($activeTab) {
    switch($activeTab) :
      case 'list':
        return YesTicketEventsList::getInstance()->render_help();
      case 'cards':
        return YesTicketEventsCards::getInstance()->render_help();
      case 'testimonials':
        return YesTicketTestimonials::getInstance()->render_help();
      case 'settings':
        return $this->ytp_admin_render_settings($activeTab)."\n".$this->ytp_admin_render_clear_cache_button();
      default:
        return YesTicketEvents::getInstance()->render_help();
    endswitch;
  }

  static public function action_settings_init(  ) { 
    return YesTicketAdminPluginPage::getInstance()->settings_init();
  }

  public function settings_init(  ) { 

    $settings_args = array(
      'type' => 'object',
      'default' => array(
        'cache_time_in_minutes' => 60,
        'yesticket_transient_keys' => array(),
        'organizer_id' => NULL,
        'api_key' => NULL,
      ),
    );
    register_setting( 'pluginPage', 'yesticket_settings', $settings_args);
  
    add_settings_section(
      'yesticket_pluginPage_section_required', 
      __("Required Settings", "yesticket"),
      'ytp_admin_render_required_settings_section_heading', 
      'pluginPage'
    );
    add_settings_field( 
      'organizer_id',
      /* translators: Please keep the quotation marks! */
      __("Your 'organizer-ID'", "yesticket"),
      'ytp_admin_render_organizer_id', 
      'pluginPage', 
      'yesticket_pluginPage_section_required' 
    );
    add_settings_field( 
      'api_key',
      /* translators: Please keep the quotation marks! */
      __("Your 'key'", "yesticket"),
      'ytp_admin_render_api_key', 
      'pluginPage', 
      'yesticket_pluginPage_section_required' 
    );
  
    add_settings_section(
      'yesticket_pluginPage_section_cache',
      __("Technical Settings", "yesticket"),
      'ytp_admin_render_technical_settings_section_heading', 
      'pluginPage'
    );
  
    add_settings_field( 
      'cache_time_in_minutes', 
      __("Cache time in minutes", "yesticket"),
      'ytp_admin_render_cache_time_in_minutes', 
      'pluginPage', 
      'yesticket_pluginPage_section_cache' 
    );
  }

  function ytp_are_necessary_settings_set() {
    $options = get_option( 'yesticket_settings' );
    $organizer_id = $options['organizer_id'];
    $api_key = $options['api_key'];
    if ($organizer_id === null || trim($organizer_id) === '') {
      return false;
    }
    if ($api_key === null || trim($api_key) === '') {
      return false;
    }
    return true;
  }
  
  function ytp_admin_render_required_settings_section_heading(  ) {
    /* translators: Points to the english website */
    $link = __("https://www.yesticket.org/login/en/integration.php#wp-plugin", "yesticket");
    /* translators: Please keep the <b>blabla</b> Markup. */
    $text = __("You need two things: your personal <b>organizer-ID</b> and the corresponding <b>Key</b>. Both can be found in your admin area on YesTicket > Marketing > Integrations:", "yesticket");
    return <<<EOD
      <p>$text</p>
      <a href='$link' target='_blank'>$link</a>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function ytp_admin_render_organizer_id(  ) { 
    $options = get_option( 'yesticket_settings' );
    $organizer_id = $options['organizer_id'];
    return <<<EOD
    <input type='number' 
           min='1' 
           step='1' 
           name='yesticket_settings[organizer_id]' 
           value='$organizer_id'>"
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function ytp_admin_render_api_key(  ) { 
    $options = get_option( 'yesticket_settings' );
    $api_key = $options['api_key'];
    return <<<EOD
      <input type='text' 
             placeholder='61dc12e43225e22add15ff1b' 
             name='yesticket_settings[api_key]' 
             value='$api_key'>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function ytp_admin_render_cache_time_in_minutes(  ) { 
    $options = get_option( 'yesticket_settings' );
    $cache_time = $options['cache_time_in_minutes'];
    return <<<EOD
      <input type='number' 
             name='yesticket_settings[cache_time_in_minutes]' 
             min="0" 
             step="1" 
             value='$cache_time'>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
}
  
  function ytp_admin_render_technical_settings_section_heading(  ) {
    echo __("Change these settings at your own risk.", "yesticket");
  }
  
  function ytp_admin_render_clear_cache_button(  ) {
    /* translators: The sentence ends with a button 'Clear Cache' (can be translated at that msgId) */
    $hint_text = __("If your changes in YesTicket are not reflected fast enough, try to: ", "yesticket");
    /* translators: Text on a button, use imperativ if possible. */
    $button_text = __("Clear Cache", "yesticket");
    return <<<EOD
    <form action="admin.php?page=yesticket-plugin" method="POST">
      <input type="hidden" name="clear_cache" value="1">
      <label for="clear_cache_submit">$hint_text</label>
      <input type="submit" name="clear_cache_submit" value="$button_text">
    </form>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function ytp_admin_render_settings($tab) {
    $action = esc_url( add_query_arg('tab', $tab, admin_url( 'options.php' )));
    $settings_fields = settings_fields( 'pluginPage' );

    ob_start();
    do_settings_sections( 'pluginPage' );
    $settings_sections = ob_get_contents();
    ob_end_flush();
    echo "setsec is: '$settings_sections'";

    ob_start();
    submit_button();
    $button = ob_get_contents();
    ob_end_flush();

    return <<<EOD
    <form method="post" action="$action">
      $settings_fields
      $settings_sections
      $button
    </form>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }
  
  function ytp_clear_cache(  ) {
      $cacheKeys = get_option( 'yesticket_transient_keys' );
      update_option( 'yesticket_transient_keys', array() );
      foreach($cacheKeys as $k) {
          delete_transient($k);
      }
      $this->ytp_admin_render_success_message(
        /* translators: Success Message after clearing cache */
        __("Deleted the cache.", "yesticket"));
  }
  
  function ytp_admin_render_feedback(  ) {
      if (isset( $_POST['clear_cache'] )) {
        $this->ytp_clear_cache();
      }
      if (isset( $_GET['settings-updated'] )) {
        $this->ytp_admin_render_success_message(
          /* translators: Success Message after updating settings */
          __("Settings saved.", "yesticket"));
      }
  }
  
  function ytp_admin_render_success_message( $msg ) {
    return "<p style='background-color: #97ff00; padding: 1rem'>$msg</p>";
  }
  
  function ytp_render_shortcode_preview( $shortcode, $previewImageFileName) {
    $image_url = ytp_getImageUrl($previewImageFileName);
    $alt_text = sprintf(
      /* translators: %s is replaced with the shortcode, e.G. 'yesticket_events' */
      __('[%s] preview', "yesticket" ),
      $shortcode
    );
    return <<<EOD
      <div class="show_on_hover shortcode_preview">
        <img src="$image_url" alt="$alt_text">
      </div>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  private function render_nagivation($activeTab) {
    $events = $this->render_navigation_tab("", $activeTab, "Events", "yesticket_events", "sample_events.png");
    $events_cards = $this->render_navigation_tab("&tab=cards", $activeTab, "Cards", "yesticket_events_cards", "sample_events_cards.png");
    $events_list = $this->render_navigation_tab("&tab=list", $activeTab, "List", "sample_events_list", "sample_events_list.png");
    $testimonials = $this->render_navigation_tab("&tab=testimonials", $activeTab, "Testimonials", "yesticket_testimonials", "sample_testimonials.png");
    $settings = $this->render_navigation_tab("&tab=settings", $activeTab, "Settings", null, null);
    return <<<EOD
    <nav class="nav-tab-wrapper">
      $events
      $events_cards
      $events_list
      $testimonials
      $settings
    </nav>
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

  private function render_navigation_tab($tabQuery, $activeTab, $tabName, $shortcode, $image) {
    $preview = "";
    if(isset($shortcode) and isset($image)) {
      $preview = $this->ytp_render_shortcode_preview($shortcode, $image);
    }
    $classIfActive = "";
    if($activeTab===$tabName) {
      $classIfActive = "nav-tab-active";
    }
    return <<<EOD
      <a href="?page=yesticket-plugin$tabQuery" 
         class="hover_trigger nav-tab $classIfActive">$tabName</a>
      $preview
EOD; // !!!! Prior to PHP 7.3, the end identifier EOD must not be indented !!!!
  }

} ?>
