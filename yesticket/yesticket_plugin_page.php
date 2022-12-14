<?php

add_action('admin_menu', 'yesticket_pluginpage_wp_menu');
add_action('admin_init', 'yesticket_settings_init');

function yesticket_pluginpage_wp_menu()
{
    add_menu_page('YesTicket', 'YesTicket', 'manage_options', 'yesticket-plugin', 'yesticket_pluginpage_init', plugin_dir_url(__FILE__) . 'img/yesticket-logo.png');
}

function yesticket_pluginpage_init()
{
    yesticket_render_feedback();?>
    <style>
                  .yt-code { background: #fff; padding: 10px; margin: 5px; font-family: monospace; border: 1px solid #eee; font-size: 1.1em; display: inline-block; }
                  h1 { margin-top: 40px; }
                  h2 { margin-top: 30px; }
                  h3 { margin-top: 20px; font-style: italic; }
                  .ml-3 { margin-left: 30px; }
                </style>
    <h1><img src='<?php echo plugin_dir_url(__FILE__) ?>img/YesTicket_logo.png' height='60' alt='YesTicket Logo'></h1>
    <p>YesTicket ist ein Ticketsystem und wir lieben Wordpress - daher hier unser Plugin. Du kannst damit deine zukünftigen Events und Zuschauerstimmen (Testimonials) per Shortcode an beliebige Stellen deiner Seite einbinden. Im Inhaltsteil, in Widgets oder in was auch immer in Wordpress.</p>
    <p>Du kannst mehrere Shortcodes in einer Seite verwenden - also z.B. erst die Liste deiner Auftritte, dann Workshops und am Ende dann Zuschauerstimmen.</p>
    
    <h2>Shortcodes für deine Events als Liste</h2>
    <p>Du benötigst 2 Dinge: deine persönliche <b>Organizer-ID</b> und deinen dazugehörigen <b>Key</b>. Beides findest du direkt zum Kopieren im Adminbereich von YesTicket > Mehr können > YesTicket einfach einbinden: <a href='https://www.yesticket.org/login/de/integration.php#wp-plugin' target='_blank'>https://www.yesticket.org/login/de/integration.php#wp-plugin</a></p>
    <p>Das sieht dann z.B. so aus: <span class="yt-code">[yesticket_events organizer="1" key="e4761c1215ff1bd225e22add" type="all" count="5" theme="light"]</span>
    <h3>Optionen für Event-Shortcodes</h3>
    <h4>Type</h4>
    <p class='ml-3'>Mit <b>type</b> kannst du die eine Liste deiner Auftritte, Workshops oder halt Auftritte und Workshops in einer Liste gemischt anzeigen.</p>
    <p class="ml-3"><span class="yt-code">type="performance"</span> nur kommende Auftritte<br>
    <span class="yt-code">type="workshop"</span> nur kommende Workshops<br>
    <span class="yt-code">type="festivals"</span> nur kommende Festivals<br>
    <span class="yt-code">type="all"</span> Workshops und Auftritte gemischt</p>
    <h4>Count</h4>
    <p class='ml-3'>Mit <b>count</b> kannst du die eine Liste begrenzen. Die eingegebene Zahl ist die Maximalzahl, sofern du so viele kommende Events angelegt hast.</p>
    <p class="ml-3"><span class="yt-code">count="5"</span> werden maximal 5 kommende Events angezeigt</p>
    <h4>Details</h4>
    <p class='ml-3'>Mit <b>details</b> kannst du die Beschreibung zu den Events anzeigen, die in YesTicket hinterlegt ist. Die sind per Link auf- und zuklappbar.</p>
    <p class="ml-3"><span class="yt-code">details="yes"</span> zeigt den Link zu Aufklappen und die Beschreibung an</p>
    <h4>Theme</h4>
    <p class='ml-3'>Mit <b>theme</b> kannst du die Farben deinem Layout ein wenig anpassen. Es gibt eine helle und eine dunkle Variante.</p>
    <p class="ml-3"><span class="yt-code">theme="light"</span> Buttons sind Hellgrau und passen zu hellen Hintergründen</p>
    <p class="ml-3"><span class="yt-code">theme="dark"</span> Buttons sind Dunkelgrau und passen zu dunklen Hintergründen</p>
    <p class="ml-3"><span class="yt-code">theme=""</span> Wenn du theme leer angibst, dann bekommst du eine simple Formatierung und Du kannst mit CSS-Formatierungen in deinem Wordpress die Formatierung selbst überschreiben - eher so die Möglichkeit für Webdesigner.</p>

    <h2>Shortcodes für deine Events als Kacheln bzw. Cards</h2>
    <p>Du benötigst 2 Dinge: deine persönliche <b>Organizer-ID</b> und deinen dazugehörigen <b>Key</b>. Beides findest du direkt zum Kopieren im Adminbereich von YesTicket > Mehr können > YesTicket einfach einbinden: <a href='https://www.yesticket.org/login/de/integration.php#wp-plugin' target='_blank'>https://www.yesticket.org/login/de/integration.php#wp-plugin</a></p>
    <p>Das sieht dann z.B. so aus: <span class="yt-code">[yesticket_events_cards organizer="1" key="e4761c1215ff1bd225e22add" type="all" details="no" count="5" theme="light"]</span>
    <h3>Optionen für Event-Shortcodes</h3>
    <h4>Type</h4>
    <p class='ml-3'>Mit <b>type</b> kannst du die eine Liste deiner Auftritte, Workshops oder halt Auftritte und Workshops in einer Liste gemischt anzeigen.</p>
    <p class="ml-3"><span class="yt-code">type="performance"</span> nur kommende Auftritte<br>
    <span class="yt-code">type="workshop"</span> nur kommende Workshops<br>
    <span class="yt-code">type="festivals"</span> nur kommende Festivals<br>
    <span class="yt-code">type="all"</span> Workshops und Auftritte gemischt</p>
    <h4>Count</h4>
    <p class='ml-3'>Mit <b>count</b> kannst du die eine Liste begrenzen. Die eingegebene Zahl ist die Maximalzahl, sofern du so viele kommende Events angelegt hast.</p>
    <p class="ml-3"><span class="yt-code">count="5"</span> werden maximal 5 kommende Events angezeigt</p>
    <p class="ml-3"><span class="yt-code">count="1"</span> bekommt spezielles CSS, sodass das eine Event schön dargestellt wird.</p>
    <h4>Grep</h4>
    <p class='ml-3'>Mit <b>grep</b> kannst du die Liste der Events über den Titel filtern.</p>
    <p class="ml-3"><span class="yt-code">grep="im Bierhaus"</span> werden nur Events angezeigt, die im Event Titel irgendwo die Zeichenfolge "im Bierhaus" enthalten.</p>
    <h4>Theme</h4>
    <p class='ml-3'>Mit <b>theme</b> kannst du die Farben deinem Layout ein wenig anpassen. Es gibt eine helle und eine dunkle Variante.</p>
    <p class="ml-3"><span class="yt-code">theme="light"</span> Buttons sind Hellgrau und passen zu hellen Hintergründen</p>
    <p class="ml-3"><span class="yt-code">theme="dark"</span> Buttons sind Dunkelgrau und passen zu dunklen Hintergründen</p>
    <p class="ml-3"><span class="yt-code">theme=""</span> Wenn du theme leer angibst, dann bekommst du eine simple Formatierung und Du kannst mit CSS-Formatierungen in deinem Wordpress die Formatierung selbst überschreiben - eher so die Möglichkeit für Webdesigner.</p>
    
    <h2>Shortcodes für Zuschauerstimmen</h2>
    <p>Du benötigst für deine Testimonials die gleichen 2 Dinge wie oben: deine persönliche <b>Organizer-ID</b> und deinen dazugehörigen <b>Key</b>. Beides findest du direkt zum Kopieren im Adminbereich von YesTicket > Marketing > YesTicket einfach einbinden: <a href='https://www.yesticket.org/login/de/integration.php#wp-plugin' target='_blank'>https://www.yesticket.org/login/de/integration.php#wp-plugin</a></p>
    <p>Das sieht dann z.B. so aus: <span class="yt-code">[yesticket_testimonials organizer="1" key="e4361c1215ff1bd225e22add" count="30"]</span></p>
    <h3>Optionen für Testimonial-Shortcodes</h3>
    <h4>Count</h4>
    <p class='ml-3'>Mit <b>count</b> kannst du die eine Liste begrenzen. Die eingegebene Zahl ist die Maximalzahl, sofern du so viele kommende Events angelegt hast.</p>
    <p class="ml-3"><span class="yt-code">count="5"</span> werden maximal 5 kommende Events angezeigt</p>

    <form action='options.php' method='post'>
    <h2>Options</h2>
    <?php
    echo settings_fields( 'pluginPage' );
    echo do_settings_sections( 'pluginPage' );
    echo submit_button();
    ?></form><?php
    echo yesticket_cache_clear_button_render();
}

function yesticket_settings_init(  ) { 

	register_setting( 'pluginPage', 'yesticket_settings' );

	add_settings_section(
		'yesticket_pluginPage_section_required', 
		__( 'Required Settings', 'yesticket' ), 
		'yesticket_settings_required_section_callback', 
		'pluginPage'
	);
	add_settings_field( 
		'organizer_id', 
		__( 'Your Organizer-ID', 'yesticket' ), 
		'yesticket_organizer_id_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_required' 
	);
	add_settings_field( 
		'api_key', 
		__( 'Your Key', 'yesticket' ), 
		'yesticket_api_key_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_required' 
	);

	add_settings_section(
		'yesticket_pluginPage_section_cache', 
		__( 'Technical Settings', 'yesticket' ), 
		'yesticket_settings_technical_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'cache_time_in_minutes', 
		__( 'Cache Time in MIN', 'yesticket' ), 
		'yesticket_cache_time_in_minutes_render', 
		'pluginPage', 
		'yesticket_pluginPage_section_cache' 
	);
}

function yesticket_settings_required_section_callback(  ) {
	echo __( 'These settings are necessary for yesticket to function.', 'yesticket' );?>
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
	echo __( 'These settings can be adjusted, if you know what you are doing.', 'yesticket' );
}

function yesticket_cache_clear_button_render(  ) {
    ?>
        <form action="admin.php?page=yesticket-plugin" method="POST">
            <input type="hidden" name="clear_cache" value="1">
            <input type="submit" value="Clear Cache">
        </form>
    <?php
}

function yesticket_clear_cache(  ) {
    $cacheKeys = get_option( 'yesticket_transient_keys' );
    update_option( 'yesticket_transient_keys', array() );
    foreach($cacheKeys as $k) {
        delete_transient($k);
    }
    yesticket_render_success_message('Cache cleared.');
}

function yesticket_render_feedback(  ) {
    if ($_POST['clear_cache']) {
        yesticket_clear_cache();
    }
    if ($_GET['settings-updated']) {
        yesticket_render_success_message('Configuration updated.');
    }
}

function yesticket_render_success_message( $msg ) {
    ?><p style="background-color: #97ff00; padding: 1rem"><?php echo $msg ?></p><?php
}
?>