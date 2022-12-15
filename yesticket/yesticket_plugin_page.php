<?php
/**
* Author: YesTicket
* Author URI: https://www.yesticket.org/
* License: GPL2
* Text Domain: yesticket
* Domain Path: /languages
*/

add_action('admin_menu', 'yesticket_pluginpage_wp_menu');
add_action('admin_init', 'yesticket_settings_init');

function yesticket_pluginpage_styles()
{
    wp_enqueue_style('yesticket', plugins_url('admin.css', __FILE__), false, 'all');
}

add_action('admin_enqueue_scripts', 'yesticket_pluginpage_styles');

function yesticket_pluginpage_wp_menu()
{
    add_menu_page('YesTicket', 'YesTicket', 'manage_options', 'yesticket-plugin', 'yesticket_pluginpage_init', ytp_getImageUrl('YesTicket_icon_small.png'));
}

function yesticket_pluginpage_init()
{
  $tab = isset($_GET['tab']) ? $_GET['tab'] : null;
  yesticket_render_feedback();
    ?>
    <h1><img src="<?php echo ytp_getImageUrl('YesTicket_logo.png') ?>" height="60" alt="YesTicket Logo"></h1>
    <p><?php echo __('introducing yesticket on admin page', 'yesticket');?></p><?php
    $options = get_option( 'yesticket_settings' );
    $renderSettingsOnly = empty($options['organizer_id'] or empty($options['api_key']));
    if ($renderSettingsOnly) {
      echo yesticket_settings_render($tab);
      return;
    }?>
    <h2><?php echo __('shortcodes', 'yesticket');?></h2>
    <p><?php echo __('can use multiple shortcodes', 'yesticket');?></p>
    <p><?php echo __('preview shortcodes on hover', 'yesticket');?></p>

    <nav class="nav-tab-wrapper">
      <a href="?page=yesticket-plugin" class="hover_trigger nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Events</a>
        <?php echo yesticket_render_shortcode_preview('yesticket_events', 'sample_events.png');?>
      <a href="?page=yesticket-plugin&tab=cards" class="hover_trigger nav-tab <?php if($tab==='cards'):?>nav-tab-active<?php endif; ?>">Cards</a>
        <?php echo yesticket_render_shortcode_preview('yesticket_events_cards', 'sample_events_cards.png');?>
      <a href="?page=yesticket-plugin&tab=list" class="hover_trigger nav-tab <?php if($tab==='list'):?>nav-tab-active<?php endif; ?>">List</a>
        <?php echo yesticket_render_shortcode_preview('yesticket_events_list', 'sample_events_list.png');?>
      <a href="?page=yesticket-plugin&tab=testimonials" class="hover_trigger nav-tab <?php if($tab==='testimonials'):?>nav-tab-active<?php endif; ?>">Testimonials</a>
        <?php echo yesticket_render_shortcode_preview('yesticket_testimonials', 'sample_testimonials.png');?>
      <a href="?page=yesticket-plugin&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Settings</a>
    </nav>

    <div class="tab-content">
      <?php switch($tab) :
        case 'list':
          echo render_yesTicketEventsListHelp();
          break;
        case 'cards':
          echo render_yesTicketEventsCardsHelp();
          break;
        case 'testimonials':
          echo render_yesTicketTestimonialsHelp();
          break;
        case 'settings':
          echo yesticket_settings_render($tab);
          echo yesticket_cache_clear_button_render();
          break;
        default:
          echo render_yesTicketEventsHelp();
          break;
      endswitch; ?>
    </div>
<?php }

function yesticket_settings_init(  ) { 

	register_setting( 'pluginPage', 'yesticket_settings' );

	add_settings_section(
		'yesticket_pluginPage_section_required', 
		__('required settings', 'yesticket'),
		'yesticket_settings_required_section_callback', 
		'pluginPage'
	);
	add_settings_field( 
		'organizer_id',
    __('your organizer-id', 'yesticket'),
		'yesticket_organizer_id_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_required' 
	);
	add_settings_field( 
		'api_key',
    __('your key', 'yesticket'),
		'yesticket_api_key_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_required' 
	);

	add_settings_section(
		'yesticket_pluginPage_section_cache',
    __('technical settings', 'yesticket'),
		'yesticket_settings_technical_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'cache_time_in_minutes', 
    __('cache time in min', 'yesticket'),
		'yesticket_cache_time_in_minutes_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_cache' 
	);
}

function yesticket_settings_required_section_callback(  ) {?>
  <p><?php echo __('what you need and where to find it', 'yesticket');?></p>
  <a href='<?php echo __('url to wordpress plugin integration', 'yesticket');?>' target='_blank'>
    <?php echo __('url to wordpress plugin integration', 'yesticket');?></a>
  <?php
}

function yesticket_organizer_id_render(  ) { 
	$options = get_option( 'yesticket_settings' );?>
	<input type='number' min='1' step='1' name='yesticket_settings[organizer_id]' value='<?php echo $options['organizer_id']; ?>'>
	<?php
}

function yesticket_api_key_render(  ) { 
	$options = get_option( 'yesticket_settings' );?>
	<input type='text' placeholder='61dc12e43225e22add15ff1b' name='yesticket_settings[api_key]' value='<?php echo $options['api_key']; ?>'>
	<?php
}

function yesticket_cache_time_in_minutes_render(  ) { 
	$options = get_option( 'yesticket_settings' );?>
	<input type='number' name='yesticket_settings[cache_time_in_minutes]' min="0" step="1" value='<?php echo $options['cache_time_in_minutes']; ?>'>
	<?php
}

function yesticket_settings_technical_section_callback(  ) {
  echo __('change settings on own risk', 'yesticket');
}

function yesticket_cache_clear_button_render(  ) {
    ?><form action="admin.php?page=yesticket-plugin" method="POST">
        <input type="hidden" name="clear_cache" value="1">
        <label for="clear_cache_submit"><?php echo __('text before clear cache button', 'yesticket');?></label>
        <input type="submit" name="clear_cache_submit" value="<?php echo __('clear cache button', 'yesticket'); ?>">
      </form><?php
}

function yesticket_settings_render($tab) {
  ?>
  <form method="post"
        action="<?php echo esc_url( add_query_arg('tab', $tab, admin_url( 'options.php' ))); ?>"><?php
  echo settings_fields( 'pluginPage' );
  echo do_settings_sections( 'pluginPage' );
  echo submit_button();
  echo '</form>';
}

function yesticket_clear_cache(  ) {
    $cacheKeys = get_option( 'yesticket_transient_keys' );
    update_option( 'yesticket_transient_keys', array() );
    foreach($cacheKeys as $k) {
        delete_transient($k);
    }
    yesticket_render_success_message('Cache gelöscht.');
}

function yesticket_render_feedback(  ) {
    if ($_POST['clear_cache']) {
        yesticket_clear_cache();
    }
    if ($_GET['settings-updated']) {
        yesticket_render_success_message('Einstellungen gespeichert.');
    }
}

function yesticket_render_success_message( $msg ) {
    ?><p style="background-color: #97ff00; padding: 1rem"><?php echo $msg ?></p><?php
}

function yesticket_render_shortcode_preview( $shortcode, $previewImageFileName) {
  ?>
  <div class="show_on_hover shortcode_preview">
    <!--<p>Preview of shortcode <span class="yt-code">[<?php echo $shortcode;?>]</span></p>-->
    <img src="<?php echo ytp_getImageUrl($previewImageFileName) ?>" alt="<?php echo $shortcode;?> preview">
  </div>
  <?php
}
?>