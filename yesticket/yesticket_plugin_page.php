<?php

add_action('admin_menu', 'ytp_pluginpage_wp_menu');
add_action('admin_init', 'ytp_settings_init');
add_action('admin_enqueue_scripts', 'ytp_admin_styles');

function ytp_admin_styles()
{
    wp_enqueue_style('yesticket', plugins_url('ytp-admin.css', __FILE__), false, 'all');
}

function ytp_pluginpage_wp_menu()
{
    add_menu_page('YesTicket', 'YesTicket', 'manage_options', 'yesticket-plugin', 'ytp_pluginpage_init', ytp_getImageUrl('YesTicket_icon_small.png'));
}

function ytp_pluginpage_init()
{
  $tab = isset($_GET['tab']) ? $_GET['tab'] : null;
  ytp_admin_render_feedback();
    ?>
    <h1><img src="<?php echo ytp_getImageUrl('YesTicket_logo.png') ?>" height="60" alt="YesTicket Logo"></h1>
    <p><?php 
    /* translators: YesTicket Plugin Page Introduction Text*/
    echo __("YesTicket is a ticketing system and we love wordpress - so here's our plugin! You can integrate upcoming events and audience feedback (testimonials) using shortcodes anywhere on your page. Be it pages, posts, widgets, ... get creative!", "yesticket");?>
    </p><?php
    if (!ytp_are_necessary_settings_set()) {
      echo ytp_admin_render_settings($tab);
      return;
    }?>
    <h2><?php echo __("Shortcodes", "yesticket");?></h2>
    <p><?php echo __("You can use multiple shortcodes on your page. For example you might start with a list of your shows, followed by your workshops and finish with testimonials of your audience.", "yesticket");?></p>
    <p><?php  
    /* translators: Hint text on plugin-page to preview different shortcodes */
    echo __("Hover above the tabs for a preview.", "yesticket");?></p>

    <nav class="nav-tab-wrapper">
      <a href="?page=yesticket-plugin" class="hover_trigger nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Events</a>
        <?php echo ytp_render_shortcode_preview('yesticket_events', 'sample_events.png');?>
      <a href="?page=yesticket-plugin&tab=cards" class="hover_trigger nav-tab <?php if($tab==='cards'):?>nav-tab-active<?php endif; ?>">Cards</a>
        <?php echo ytp_render_shortcode_preview('yesticket_events_cards', 'sample_events_cards.png');?>
      <a href="?page=yesticket-plugin&tab=list" class="hover_trigger nav-tab <?php if($tab==='list'):?>nav-tab-active<?php endif; ?>">List</a>
        <?php echo ytp_render_shortcode_preview('yesticket_events_list', 'sample_events_list.png');?>
      <a href="?page=yesticket-plugin&tab=testimonials" class="hover_trigger nav-tab <?php if($tab==='testimonials'):?>nav-tab-active<?php endif; ?>">Testimonials</a>
        <?php echo ytp_render_shortcode_preview('yesticket_testimonials', 'sample_testimonials.png');?>
      <a href="?page=yesticket-plugin&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Settings</a>
    </nav>

    <div class="tab-content">
      <?php switch($tab) :
        case 'list':
          echo ytp_render_eventListHelp();
          break;
        case 'cards':
          echo YesTicketEventsCards::getInstance()->render_help();
          break;
        case 'testimonials':
          echo ytp_render_testimonialsHelp();
          break;
        case 'settings':
          echo ytp_admin_render_settings($tab);
          echo ytp_admin_render_clear_cache_button();
          break;
        default:
          echo YesTicketEvents::getInstance()->render_help();
          break;
      endswitch; ?>
    </div>
<?php }

function ytp_settings_init(  ) { 

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
  $link = __("https://www.yesticket.org/login/en/integration.php#wp-plugin", "yesticket");?>
  <p><?php 
  /* translators: Please keep the <b>blabla</b> Markup. */
  echo __("You need two things: your personal <b>organizer-ID</b> and the corresponding <b>Key</b>. Both can be found in your admin area on YesTicket > Marketing > Integrations:", "yesticket");?></p>
  <a href='<?php echo $link;?>' target='_blank'><?php echo $link;?></a>
  <?php
}

function ytp_admin_render_organizer_id(  ) { 
	$options = get_option( 'yesticket_settings' );?>
	<input type='number' min='1' step='1' name='yesticket_settings[organizer_id]' value='<?php echo $options['organizer_id']; ?>'>
	<?php
}

function ytp_admin_render_api_key(  ) { 
	$options = get_option( 'yesticket_settings' );?>
	<input type='text' placeholder='61dc12e43225e22add15ff1b' name='yesticket_settings[api_key]' value='<?php echo $options['api_key']; ?>'>
	<?php
}

function ytp_admin_render_cache_time_in_minutes(  ) { 
	$options = get_option( 'yesticket_settings' );?>
	<input type='number' name='yesticket_settings[cache_time_in_minutes]' min="0" step="1" value='<?php echo $options['cache_time_in_minutes']; ?>'>
	<?php
}

function ytp_admin_render_technical_settings_section_heading(  ) {
  echo __("Change these settings at your own risk.", "yesticket");
}

function ytp_admin_render_clear_cache_button(  ) {
    ?><form action="admin.php?page=yesticket-plugin" method="POST">
        <input type="hidden" name="clear_cache" value="1">
        <label for="clear_cache_submit"><?php 
        /* translators: The sentence ends with a button 'Clear Cache' (can be translated at that msgId) */
        echo __("If your changes in YesTicket are not reflected fast enough, try to: ", "yesticket");?></label>
        <input type="submit" name="clear_cache_submit" value="<?php
        /* translators: Text on a button, use imperativ if possible. */
        echo __("Clear Cache", "yesticket"); ?>">
      </form><?php
}

function ytp_admin_render_settings($tab) {
  ?>
  <form method="post"
        action="<?php echo esc_url( add_query_arg('tab', $tab, admin_url( 'options.php' ))); ?>"><?php
  echo settings_fields( 'pluginPage' );
  echo do_settings_sections( 'pluginPage' );
  echo submit_button();
  echo '</form>';
}

function ytp_clear_cache(  ) {
    $cacheKeys = get_option( 'yesticket_transient_keys' );
    update_option( 'yesticket_transient_keys', array() );
    foreach($cacheKeys as $k) {
        delete_transient($k);
    }
    ytp_admin_render_success_message(
      /* translators: Success Message after clearing cache */
      __("Deleted the cache.", "yesticket"));
}

function ytp_admin_render_feedback(  ) {
    if (isset( $_POST['clear_cache'] )) {
        ytp_clear_cache();
    }
    if (isset( $_GET['settings-updated'] )) {
      ytp_admin_render_success_message(
        /* translators: Success Message after updating settings */
        __("Settings saved.", "yesticket"));
    }
}

function ytp_admin_render_success_message( $msg ) {
    ?><p style="background-color: #97ff00; padding: 1rem"><?php echo $msg ?></p><?php
}

function ytp_render_shortcode_preview( $shortcode, $previewImageFileName) {
  ?>
  <div class="show_on_hover shortcode_preview">
    <img src="<?php echo ytp_getImageUrl($previewImageFileName) ?>" alt="<?php
    printf(
      /* translators: %s is replaced with the shortcode, e.G. 'yesticket_events' */
      __('[%s] preview', "yesticket" ),
      $shortcode
    );?>">
  </div>
  <?php
}
?>