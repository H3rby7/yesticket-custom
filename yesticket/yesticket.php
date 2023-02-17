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

add_action('init', 'ytp_init_callback');

function ytp_init_callback() {
    wp_register_style('yesticket', plugins_url('ytp-site.css', __FILE__), false, 'all');
    wp_register_style('yesticket-admin', plugins_url('admin/styles.css', __FILE__), false, 'all');
    load_plugin_textdomain( 'yesticket', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
    
?>