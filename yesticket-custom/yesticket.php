<?php
/**
* Plugin Name: YesTicketCustom
* Plugin URI: ?page=yesticket-custom-plugin
* Version: 1.0.3
* Author: Joka
* Author URI: https://github.com/H3rby7
* Description: Onlineticketing
* License: GPL2
* Text Domain: yesticket-custom
*/

function my_styles() {
	wp_enqueue_style( 'yesticket-custom', plugins_url( 'front.css', __FILE__ ), false, 'all' );
	// wp_enqueue_script('yesticket-custom', plugins_url( 'front.js', __FILE__ ) );
}

add_action('wp_enqueue_scripts', 'my_styles');

///////// YesTicket Shortcodes:

function getYesTicketCustomEvents($atts) {
	$att = shortcode_atts( array(
			'organizer' => '',
			'key' => '',
			'details' => 'no',
			'type' => 'all',
			'env' => 'prod',
			'count' => '100',
			'theme' => 'light',
			), $atts );
	$content = "";
	$env_add = "";
	if ($att["env"] == 'dev') $env_add = "/dev";
	if (empty($att["organizer"])) return "FEHLER: Bitte gib das Organizer-Attribut an. Dieses kannst Du direkt von der Einbinden-Seite auf YesTicket übernehmen.";
	if (empty($att["key"])) return "FEHLER: Bitte gib das Key-Attribut an. Dieses kannst Du direkt von der Einbinden-Seite auf YesTicket übernehmen.";
	if ($att["type"]=="Impro-Show")  $att["type"] = "performance";
	if ($att["type"]=="Workshop")  $att["type"] = "workshop";
	if ($att["type"]=="Festival")  $att["type"] = "festival";
	if ($att["type"]!="all" AND $att["type"]!="performance" AND $att["type"]!="workshop" AND $att["type"]!="festival") return "FEHLER: Bitte gib ein korrektes Type-Attribut an. Gültig sind nur all, performance, workshop und festival. Wenn Du alle Events möchtest gib das Attribut einfach nicht an";
	// Get it from API URL:
	$get_url = "https://www.yesticket.org".$env_add."/api/events-endpoint.php?organizer=".$att["organizer"]."&type=".$att["type"]."&key=".$att["key"];
	if (function_exists('curl_version'))
	{
		$ch = curl_init();
		$timeout = 4;
		curl_setopt($ch, CURLOPT_URL, $get_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$get_content = curl_exec($ch);
		curl_close($ch);
	}
	else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
	{
		ini_set('default_socket_timeout', 4);
		$ctx = stream_context_create(array('http'=>
		array(
		'timeout' => 4,  //5 seconds
		)
		));
		$get_content = file_get_contents($get_url, 0, $ctx);
	}
	else
	{
		return 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl. Bitte gehen Sie dazu auf ihren Webhosting-Provider zu.';
	}
	if (empty($get_content)) return "Im Moment sind unsere Veranstaltungen nicht erreichbar. Versucht es bitte später noch einmal.";
	$result = json_decode($get_content);

	//return(json_last_error());
	//////////

	if ($att["theme"] == "light") {
	    $content .= "<div class='yt-light'>";
	}
	else if ($att["theme"] == "dark") {
	    $content .= "<div class='yt-dark'>";
	}
	else {
		$content .= "<div class='yt-default ".$att["theme"]."'>";
	}

	if (count($result) > 0 && $result->message != "no items found"){
		$count = 0;
		foreach($result as $item){
			$add = "";
			$content .= "<div class='yt-row'>";
			if ($att["type"]=="all") $add = " <span class='yt-eventtype'>".$item->event_type."</span>";
			$content .= '<a href="'.$item->yesticket_booking_url.'" target="_blank" class="yt-button">Tickets '.'<img src="https://www.yesticket.org/img/YesTicket_260x260.png" height="20" width="20">'.'</a>';
			$content .= "<span class='yt-eventdate'>".date('d.m.y H:i', strtotime($item->event_datetime))." Uhr</span>".$add;
			//$content .= '<img src="https://www.yesticket.org/img/YesTicket_260x260.png" height="50" width="50">';
			//$content .= "<a href='".$item->yesticket_booking_url."'>".$item->event_name."</a>";
			$content .= "<span class='yt-eventname'>".htmlentities($item->event_name)."</span>";

			$content .= "<span class='yt-eventdate'>".htmlentities($item->location_name).", ".htmlentities($item->location_city)."</span>";
			if (!empty($item->event_urgency_string)) $content.= "<br><span class='yt-urgency'>".htmlentities($item->event_urgency_string).""."</span>";
			if ($att["details"] == "yes") {
				$details = nl2br(htmlentities($item->event_description))."<br><br>";
				if (!empty($item->event_notes_help)) $details .= "Hinweise: ".nl2br(htmlentities($item->event_notes_help))."<br><br>";
				$details .= "Tickets:<br>".htmlentities($item->tickets)."<br><br>";
				$details .= "Spielort:<br>".htmlentities($item->location_name)."<br>".htmlentities($item->location_street)."<br>".htmlentities($item->location_zip)." ".htmlentities($item->location_city).", ".htmlentities($item->location_state).", ".htmlentities($item->location_country);
				$content .= "<br><details>
							  <summary><a>Details anzeigen</a></summary>
							  <p>".$details.'</p><div class="yt-button-row"><a href="'.$item->yesticket_booking_url.'" target="_blank" class="yt-button-big">Tickets ordern<img src="https://www.yesticket.org/img/YesTicket_260x260.png" height="20" width="20">'.'</a></div>'."
							</details>";
			}
			$content .= "</div>\n";
			$count++;
			if ($count == (int)$att["count"]) break;
		}
	} else {
		$content = "<div><p>Im Moment keine aktuellen Veranstaltungen.</p>";
	}
	$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p></div>";
	return $content;
}

add_shortcode('yesticket_custom_events', 'getYesTicketCustomEvents');

// WP Backend Plugin Page
add_action('admin_menu', 'pluginpage_wp_menu');

function pluginpage_wp_menu(){
        add_menu_page( 'YesTicketCustom', 'YesTicket Custom', 'manage_options', 'yesticket-plugin', 'pluginpage_init', plugin_dir_url( __FILE__ ) . 'img/yesticket-logo.png' );
}

function pluginpage_init(){
				echo "<style>
				  .yt-code { background: #fff; padding: 10px; margin: 5px; font-family: monospace; border: 1px solid #eee; font-size: 1.1em; display: inline-block; }
				  h1 { margin-top: 40px; }
				  h2 { margin-top: 30px; }
				  h3 { margin-top: 20px; font-style: italic; }
				  .ml-3 { margin-left: 30px; }
				</style>";
        echo "<h1><img src='https://www.yesticket.org/img/YesTicket_logo.png' height='60' alt='YesTicket Logo'></h1>";
        echo "<p><b>Custom Version - For event shortcode WITH images!</b></p>";
        echo "<p>YesTicket ist ein Ticketsystem und wir lieben Wordpress - daher hier unser Plugin. Du kannst damit deine zukünftigen Events und Zuschauerstimmen (Testimonials) per Shortcode an beliebige Stellen deiner Seite einbinden. Im Inhaltsteil, in Widgets oder in was auch immer in Wordpress.</p>";
        echo "<p>Du kannst mehrere Shortcodes in einer Seite verwenden - also z.B. erst die Liste deiner Auftritte, dann Workshops und am Ende dann Zuschauerstimmen.</p>";
        echo "<h2>Shortcodes für Events</h2>";
        echo "<p>Du benötigst 2 Dinge: deine persönliche <b>Organizer-ID</b> und deinen dazugehörigen <b>Key</b>. Beides findest du direkt zum Kopieren im Adminbereich von YesTicket > Mehr können > YesTicket einfach einbinden: <a href='https://www.yesticket.org/login/de/integration.php#wp-plugin' target='_blank'>https://www.yesticket.org/login/de/integration.php#wp-plugin</a></p>";
        echo '<p>Das sieht dann z.B. so aus: <span class="yt-code">[yesticket_custom_events organizer="1" key="e4761c1215ff1bd225e22add" type="all" count="5" theme="light"]</span>';
        echo "<h3>Optionen für Event-Shortcodes</h3>";
        echo "<h4>Type</h4>";
        echo "<p class='ml-3'>Mit <b>type</b> kannst du die eine Liste deiner Auftritte, Workshops oder halt Auftritte und Workshops in einer Liste gemischt anzeigen.</p>";
        echo '<p class="ml-3"><span class="yt-code">type="performance"</span> nur kommende Auftritte<br>';
        echo '<span class="yt-code">type="workshop"</span> nur kommende Workshops<br>';
				echo '<span class="yt-code">type="festivals"</span> nur kommende Festivals<br>';
        echo '<span class="yt-code">type="all"</span> Workshops und Auftritte gemischt</p>';
        echo "<h4>Count</h4>";
        echo "<p class='ml-3'>Mit <b>count</b> kannst du die eine Liste begrenzen. Die eingegebene Zahl ist die Maximalzahl, sofern du so viele kommende Events angelegt hast.</p>";
        echo '<p class="ml-3"><span class="yt-code">count="5"</span> werden maximal 5 kommende Events angezeigt</p>';
        echo "<h4>Details</h4>";
        echo "<p class='ml-3'>Mit <b>details</b> kannst du die Beschreibung zu den Events anzeigen, die in YesTicket hinterlegt ist. Die sind per Link auf- und zuklappbar.</p>";
        echo '<p class="ml-3"><span class="yt-code">details="yes"</span> zeigt den Link zu Aufklappen und die Beschreibung an</p>';
        echo "<h4>Theme</h4>";
        echo "<p class='ml-3'>Mit <b>theme</b> kannst du die Farben deinem Layout ein wenig anpassen. Es gibt eine helle und eine dunkle Variante.</p>";
        echo '<p class="ml-3"><span class="yt-code">theme="light"</span> Buttons sind Hellgrau und passen zu hellen Hintergründen</p>';
        echo '<p class="ml-3"><span class="yt-code">theme="dark"</span> Buttons sind Dunkelgrau und passen zu dunklen Hintergründen</p>';
				echo '<p class="ml-3"><span class="yt-code">theme=""</span> Wenn du theme leer angibst, dann bekommst du eine simple Formatierung und Du kannst mit CSS-Formatierungen in deinem Wordpress die Formatierung selbst überschreiben - eher so die Möglichkeit für Webdesigner.</p>';
}

?>
