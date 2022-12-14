<?php

add_action('admin_menu', 'yesticket_pluginpage_wp_menu');
add_action('admin_init', 'yesticket_settings_init');

function yesticket_pluginpage_wp_menu()
{
    add_menu_page('YesTicket', 'YesTicket', 'manage_options', 'yesticket-plugin', 'yesticket_pluginpage_init', plugin_dir_url(__FILE__) . 'img/yesticket-logo.png');
}

function yesticket_pluginpage_init()
{
  $tab = isset($_GET['tab']) ? $_GET['tab'] : null;
  yesticket_render_feedback();
    ?><style>
      .yt-code { 
        background: #fff; 
        padding: 10px; 
        margin: 5px; 
        font-family: monospace; 
        border: 1px solid #eee; 
        font-size: 1.1em; 
        display: inline-block;
      }
      h1 { margin-top: 40px; }
      h2 { margin-top: 30px; }
      h3 { margin-top: 20px; font-style: italic; }
      .ml-3 { margin-left: 30px; }
    </style>
    <h1><img src='<?php echo plugin_dir_url(__FILE__) ?>img/YesTicket_logo.png' height='60' alt='YesTicket Logo'></h1>
    <p><?php echo __('YesTicket ist ein Ticketsystem und wir lieben Wordpress - daher hier unser Plugin. Du kannst damit deine zukünftigen Events und Zuschauerstimmen (Testimonials) per Shortcode an beliebige Stellen deiner Seite einbinden. Im Inhaltsteil, in Widgets oder in was auch immer in Wordpress.', 'yesticket');?></p>
    <?php
    $options = get_option( 'yesticket_settings' );
    $renderSettingsOnly = empty($options['organizer_id'] or empty($options['api_key']));
    if ($renderSettingsOnly) {
      echo yesticket_settings_render($tab);
      return;
    }?>
    <h2>Shortcodes</h2>
    <p>Du kannst mehrere Shortcodes in einer Seite verwenden - also z.B. erst die Liste deiner Auftritte, dann Workshops und am Ende dann Zuschauerstimmen.</p>

    <nav class="nav-tab-wrapper">
      <a href="?page=yesticket-plugin" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Events</a>
      <a href="?page=yesticket-plugin&tab=cards" class="nav-tab <?php if($tab==='cards'):?>nav-tab-active<?php endif; ?>">Cards</a>
      <a href="?page=yesticket-plugin&tab=list" class="nav-tab <?php if($tab==='list'):?>nav-tab-active<?php endif; ?>">List</a>
      <a href="?page=yesticket-plugin&tab=testimonials" class="nav-tab <?php if($tab==='testimonials'):?>nav-tab-active<?php endif; ?>">Testimonials</a>
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
		__( 'Obligatorische Einstellungen', 'yesticket' ), 
		'yesticket_settings_required_section_callback', 
		'pluginPage'
	);
	add_settings_field( 
		'organizer_id', 
		__( 'Deine Organizer-ID', 'yesticket' ), 
		'yesticket_organizer_id_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_required' 
	);
	add_settings_field( 
		'api_key', 
		__( 'Dein "Key"', 'yesticket' ), 
		'yesticket_api_key_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_required' 
	);

	add_settings_section(
		'yesticket_pluginPage_section_cache', 
		__( 'Technische Einstellungen', 'yesticket' ), 
		'yesticket_settings_technical_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'cache_time_in_minutes', 
		__( 'Cache Zeit in MIN', 'yesticket' ), 
		'yesticket_cache_time_in_minutes_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_cache' 
	);
}

function yesticket_settings_required_section_callback(  ) {?>
    <p><?php echo __('Du benötigst 2 Dinge: deine persönliche', 'yesticket');?> 
    <b>Organizer-ID</b>
    <?php echo __('und deinen dazugehörigen', 'yesticket');?>
    <b>Key</b>.
    <?php echo __('Beides findest du direkt zum Kopieren im Adminbereich von YesTicket > Mehr können > YesTicket einfach einbinden', 'yesticket');?>:
    <a href='https://www.yesticket.org/login/<?php echo __('de', 'yesticket');?>/integration.php#wp-plugin' target='_blank'>
        https://www.yesticket.org/login/<?php echo __('de', 'yesticket');?>/integration.php#wp-plugin</a></p>
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
	echo __( 'Diese Einstellungen kannst du anpassen, wenn du weißt, was du tust.', 'yesticket' );
}

function yesticket_cache_clear_button_render(  ) {
    ?><form action="admin.php?page=yesticket-plugin" method="POST">
        <input type="hidden" name="clear_cache" value="1">
        <label for="clear_cache_submit"><?php echo __('Wenn sich deine Einträge nicht schnell genug updaten, versuche es mal mit: ', 'yesticket'); ?></label>
        <input type="submit" name="clear_cache_submit" value="Cache löschen">
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
?>