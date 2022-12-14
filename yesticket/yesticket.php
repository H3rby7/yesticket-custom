<?php
/**
* Plugin Name: YesTicket
* Plugin URI: ?page=yesticket-plugin
* Version: 1.1.0
* Author: YesTicket
* Author URI: https://www.yesticket.org/
* Description: Onlineticketing
* License: GPL2
* Text Domain: yesticket
*/

function yesticket_styles()
{
    wp_enqueue_style('yesticket', plugins_url('front.css', __FILE__), false, 'all');
    // wp_enqueue_script('yesticket', plugins_url('front.js', __FILE__));
}

add_action('wp_enqueue_scripts', 'yesticket_styles');

if (!function_exists('is_countable')) {
    function is_countable($var)
    {
        return (is_array($var) || $var instanceof Countable);
    }
}

// add_option('yesticket_cache_time_in_minutes', 60);
// add_option('yesticket_transient_keys', array());

function getDataCached($get_url) {
    $options = get_option('yesticket_settings');
    $CACHE_TIME_IN_MINUTES = $options['cache_time_in_minutes'];
    $CACHE_KEY = cacheKey($get_url);

    // check if we have cached information
    $data = get_transient($CACHE_KEY);
    if( false === $data ) {
        // Cache not present, we make the API call
        $data = getData($get_url);
        set_transient($CACHE_KEY, $data, $CACHE_TIME_IN_MINUTES * MINUTE_IN_SECONDS );
        // save cache key to options, so we can delete the transient, if necessary
        addCacheKeyToOptions($CACHE_KEY);
    }
    // at this time we have our data, either from cache or after an API call.
    return $data;
}

function cacheKey($get_url) {
    // common key specific to yesticket to set and retrieve WP_TRANSIENTS
    return 'yesticket_' . md5($get_url);
}

function addCacheKeyToOptions($CACHE_KEY) {
    $cacheKeys = get_option('yesticket_transient_keys', array());
    if (!in_array($CACHE_KEY, $cacheKeys)) {
        // unknown cache key, add to known keys
        $cacheKeys[] = $CACHE_KEY;
        update_option('yesticket_transient_keys', $cacheKeys);
    }
}

function getData($get_url) {
    if (function_exists('curl_version')) {
        $ch = curl_init();
        $timeout = 4;
        curl_setopt($ch, CURLOPT_URL, $get_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $get_content = curl_exec($ch);
        curl_close($ch);
    } elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
        ini_set('default_socket_timeout', 4);
        $ctx = stream_context_create(array('http'=>
        array(
        'timeout' => 4,  //5 seconds
        )
        ));
        $get_content = file_get_contents($get_url, 0, $ctx);
    } else {
        throw new Exception('Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl. Bitte gehen Sie dazu auf ihren Webhosting-Provider zu.');
    }
    if (empty($get_content) && file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
        // in Case of a CURL-error
        ini_set('default_socket_timeout', 4);
        $ctx = stream_context_create(array('http'=>
            array(
            'timeout' => 4,  //5 seconds
            )
            ));
        $get_content = file_get_contents($get_url, 0, $ctx);
    }
    if (empty($get_content)) {
        throw new RuntimeException("Im Moment sind unsere Veranstaltungen nicht erreichbar. Versucht es bitte später noch einmal.");
    }
    $result = json_decode($get_content);
    //return(json_last_error());
    return $result;
}

function validateArguments($att, $options) {
    // We prefer people setting their private info in the settings, rather than the shortcode.
    if (empty($options["organizer_id"]) and empty($att["organizer"])) {
        throw new InvalidArgumentException("Bitte konfiguriere deine Organizer-ID in den Plugin Settings.");
    }
    if (empty($options["api_key"]) and empty($att["key"])) {
        throw new InvalidArgumentException("Bitte konfiguriere deinen Key in den Plugin Settings.");
    }
    if (!empty($att["type"]) and $att["type"]!="all" and $att["type"]!="performance" and $att["type"]!="workshop" and $att["type"]!="festival") {
        throw new InvalidArgumentException("Bitte gib ein korrektes Type-Attribut an. Gültig sind nur all, performance, workshop und festival. Wenn Du alle Events möchtest gib das Attribut einfach nicht an.");
    }
    return true;
}

function getEventsFromApi($att) {
    $env_add = "";
    if ($att["env"] == 'dev') {
        $env_add = "/dev";
    }
    $options = get_option('yesticket_settings');
    validateArguments($att, $options);
    // Get it from API URL:
    $get_url = "https://www.yesticket.org".$env_add."/api/events-endpoint.php";
    $get_url .= buildYesticketQueryParams($atts, $options);
    return getDataCached($get_url);
}

function buildYesticketQueryParams($atts, $options) {
    $queryParams = '';
    if (!empty($att["organizer"])) {
        $queryParams .= '?organizer='.$att["organizer"];
    } else {
        $queryParams .= '?organizer='.$options["organizer_id"];
    }
    if (!empty($att["key"])) {
        $queryParams .= '&key='.$att["key"];
    } else {
        $queryParams .= '&key='.$options["api_key"];
    }
    if (!empty($att["count"])) {
        $queryParams .= '&count='.$att["count"];
    }
    if (!empty($att["type"])) {
        $queryParams .= '&type='.$att["type"];
    }
    return $queryParams;
}

///////// YesTicket Shortcodes:

function getYesTicketEvents($atts)
{
    $att = shortcode_atts(array(
                    'organizer' => '',
                    'key' => '',
                    'details' => 'no',
                    'type' => 'all',
                    'env' => 'prod',
                    'count' => '100',
                    'theme' => 'light',
                    ), $atts);
    $content = "";
    try {
        $result = getEventsFromApi($att);
        if ($att["theme"] == "light") {
            $content .= "<div class='yt-light'>";
        } elseif ($att["theme"] == "dark") {
            $content .= "<div class='yt-dark'>";
        } else {
            $content .= "<div class='yt-default ".$att["theme"]."'>";
        }
        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            foreach ($result as $item) {
                $add = "";
                $content .= "<div class='yt-row'>";
                if ($att["type"]=="all") {
                    $add = " <span class='yt-eventtype'>".$item->event_type."</span>";
                }
                $content .= '<a href="'.$item->yesticket_booking_url.'" target="_blank" class="yt-button">Tickets '.'<img src="'.plugin_dir_url(__FILE__) .'img/YesTicket_260x260.png" height="20" width="20">'.'</a>';
                $content .= "<span class='yt-eventdate'>".date('d.m.y H:i', strtotime($item->event_datetime))." Uhr</span>".$add;
                $content .= "<span class='yt-eventname'>".htmlentities($item->event_name)."</span>";
    
                $content .= "<span class='yt-eventdate'>".htmlentities($item->location_name).", ".htmlentities($item->location_city)."</span>";
                if (!empty($item->event_urgency_string)) {
                    $content.= "<br><span class='yt-urgency'>".htmlentities($item->event_urgency_string).""."</span>";
                }
                if ($att["details"] == "yes") {
                    $details = nl2br(htmlentities($item->event_description))."<br><br>";
                    if (!empty($item->event_notes_help)) {
                        $details .= "Hinweise: ".nl2br(htmlentities($item->event_notes_help))."<br><br>";
                    }
                    $details .= "Tickets:<br>".htmlentities($item->tickets)."<br><br>";
                    $details .= "Spielort:<br>".htmlentities($item->location_name)."<br>".htmlentities($item->location_street)."<br>".htmlentities($item->location_zip)." ".htmlentities($item->location_city).", ".htmlentities($item->location_state).", ".htmlentities($item->location_country);
                    $content .= "<br><details>
                                  <summary><u>Details anzeigen</u></summary>
                                  <p>".$details.'</p><div class="yt-button-row"><a href="'.$item->yesticket_booking_url.'" target="_blank" class="yt-button-big">Tickets ordern<img src="'.plugin_dir_url(__FILE__) . 'img/YesTicket_260x260.png'.'" height="20" width="20">'.'</a></div>'."
                                </details>";
                }
                $content .= "</div>\n";
                $count++;
                if ($count == (int)$att["count"]) {
                    break;
                }
            }
        } else {
            $content = "<p>Im Moment keine aktuellen Veranstaltungen.</p>";
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function getYesTicketEventsCards($atts) {
	$att = shortcode_atts( array(
			'organizer' => '',
			'key' => '',
            'details' => 'no',
			'type' => 'all',
			'env' => 'prod',
			'count' => '100',
			'grep' => '',
			'theme' => 'light',
			), $atts );
    try {
        $result = getEventsFromApi($att);
        $content = "";
        if ($att["theme"] == "light") {
                $content .= "<div class='yt-light'>";
        }
        else if ($att["theme"] == "dark") {
                $content .= "<div class='yt-dark'>";
        }
        else {
            $content .= "<div class='yt-default ".$att["theme"]."'>";
        }

        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            if ((int)$att["count"] === 1) {
                $content .= "<div class='yt-single'>\n";
            } else {
                $content .= "<div class='yt-container'>\n";
            }
            foreach($result as $item){
                if (!empty($att["grep"])) {
                    if (!str_contains($item->event_name, $att["grep"])) {
                        // Did not find the required Substring in the event_title, skip this event
                        continue;
                    }
                }
                $time = strtotime($item->event_datetime);
                $content .= '<div class="yt-card-event">'."\n".'<a href="'.$item->yesticket_booking_url.'" target="_new">'."\n".'<div class="yt-card">';
                    // START 'Wrapper' [div > a > div(yt-card)]
                    // START 'img'
                    $content .= '<div class="yt-card-image-wrapper">'."\n";
                        $content .= '<img src="'.$item->event_picture_url.'" alt="Eventbild">'."\n";
                    $content .= '</div>'."\n";
                    // END 'img'
                    // START 'text'
                    $content .= '<div class="yt-card-text-wrapper">'."\n";
                        // START 'DATE'
                        $content .= '<div class="yt-card-date">'."\n";
                            $content .= '<span class="yt-card-month">'.date('M', $time).'</span><br>'."\n";
                            $content .= '<strong class="yt-card-day">'.date('d', $time).'</strong><br>'."\n";
                            $content .= '<span class="yt-card-year">'.date('Y', $time).'</span>'."\n";
                        $content .= '</div>'."\n";
                        // END 'DATE'
                        // START 'Body // The Event'
                        $content .= '<div class="yt-card-body">'."\n";
                            $content .= '<span class="yt-card-body-organizer">'.htmlentities($item->organizer_name).'</span><br>'."\n";
                            $content .= '<strong class="yt-card-body-title">'.htmlentities($item->event_name).'</strong><br>'."\n";
                            $content .= '<small class="yt-card-body-location">'.htmlentities($item->location_name).'</small>'."\n";
                        $content .= '</div>'."\n";
                        // END 'Body // The Event'
                    $content .= '</div>'."\n";
                    // END 'text'
                $content .= "</div>\n</a>\n</div>";
                // END 'Wrapper' [div > a > div(yt-card)]
                $count++;
                if ($count == (int)$att["count"]) break;
            }
            $content .= "</div>\n";
        } else {
            $content = "<p>Im Moment keine aktuellen Veranstaltungen.</p>";
        }
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function getYesTicketEventsList($atts)
{
    $att = shortcode_atts(array(
                    'organizer' => '',
                    'key' => '',
                    'ticketlink' => 'no',
                    'type' => 'all',
                    'env' => 'prod',
                    'count' => '100',
                    'theme' => 'light',
                    ), $atts);
    try {
        $result = getEventsFromApi($att);
        $content = "";

        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            foreach ($result as $item) {
                $add = "";
                $content .= "<div class='yt-row-list'>";
                if ($att["type"]=="all") {
                    $add = "<br><span class='yt-eventtype'>".$item->event_type."</span>";
                }
                $content .= "<span class='yt-eventdate'>".date('d.m.y H:i', strtotime($item->event_datetime))." Uhr</span>".$add."</span><br>";
                $content .= "<span class='yt-eventname'>".htmlentities($item->event_name)."</span>";

                $content .= "<span class='yt-eventdate'>".htmlentities($item->location_name).", ".htmlentities($item->location_city)."</span>";
                if ($att["ticketlink"]=="yes") {
                    $content .= '<br><a href="'.$item->yesticket_booking_url.'" target="_blank">Tickets</a>';
                }
                $content .= "</div>\n";
                $count++;
                if ($count == (int)$att["count"]) {
                    break;
                }
            }
        } else {
            $content = "<div><p>Im Moment keine aktuellen Veranstaltungen.</p>";
        }
        //$content .= "<p>Wir nutzen das Ticketsystem von <a href='https://www.yesticket.org' target='_blank'>YesTicket.org</a></p>";
        $content .= "</div>";
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

function getYesTicketTestimonials($atts)
{
    $att = shortcode_atts(array(
                    'organizer' => '',
                    'key' => '',
                    'count' => '3',
                    'type' => 'all',
                    'details' => 'no',
                    'env' => 'prod',
                    'theme' => 'light',
                    ), $atts);
    $content = "";
    $env_add = "";
    if ($att["env"] == 'dev') {
        $env_add = "/dev";
    }
    try {
        $options = get_option('yesticket_settings');
        validateArguments($att, $options);
        // Get it from API URL:
        $get_url = "https://www.yesticket.org".$env_add."/api/v2/testimonials.php";
        $get_url .= buildYesticketQueryParams($atts, $options);
        $result = getDataCached($get_url);
        //////////

        if (count((is_countable($result) ? $result : [])) > 0 && $result->message != "no items found") {
            $count = 0;
            foreach ($result as $item) {
                $add = "";
                $content .= "<div class='yt-testimonial-row'>";
                if (!empty($item->event_name) && $att["details"] == "yes") {
                    $add_event = "<br><span class='yt-testimonial-source'>über ".htmlentities($item->event_name)."</span>";
                }
                $content .= "<span class='yt-testimonial-text'>&raquo;".htmlentities($item->text).'&laquo;</span><br>'."<span class='yt-testimonial-source'>".htmlentities($item->source).' '."</span> <span class='yt-testimonial-date'>Am ".htmlentities(date('d.m.Y', strtotime($item->date)))."</span>".$add_event;
                $content .= "</div>\n";
                $count++;
                if ($count == (int)$att["count"]) {
                    break;
                }
            }
        } else {
            $content = "";
        }
    } catch (Exception $e) {
        $content .= __($e->getMessage(), 'yesticket');
    }
    return $content;
}

add_shortcode('yesticket_events', 'getYesTicketEvents');
add_shortcode('yesticket_events_cards', 'getYesTicketEventsCards');
add_shortcode('yesticket_events_list', 'getYesTicketEventsList');
add_shortcode('yesticket_testimonials', 'getYesTicketTestimonials');

add_option('yesticket_transient_keys', array());

// WP Backend Plugin Page
add_action('admin_menu', 'yesticket_pluginpage_wp_menu');
add_action( 'admin_init', 'yesticket_settings_init' );

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