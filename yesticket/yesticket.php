<?php
/**
* Plugin Name: YesTicket
* Plugin URI: ?page=yesticket-plugin
* Version: 2.0.0
* Author: YesTicket
* Author URI: https://www.yesticket.org/
* Description: Online Ticketing
* License: GPL2
* Text Domain: yesticket
* Domain Path: /languages
*/

include_once "admin/plugin_menu.php";
include_once "shortcodes/yesticket_events.php";
include_once "shortcodes/yesticket_events_list.php";
include_once "shortcodes/yesticket_events_cards.php";
include_once "shortcodes/yesticket_testimonials.php";

add_action('wp_enqueue_scripts', 'ytp_site_styles');
add_action('init', 'ytp_load_textdomain');

function ytp_site_styles()
{
    wp_enqueue_style('yesticket', plugins_url('ytp-site.css', __FILE__), false, 'all');
    // wp_enqueue_script('yesticket', plugins_url('front.js', __FILE__));
}

function ytp_load_textdomain() {
    load_plugin_textdomain( 'yesticket', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
    
?>