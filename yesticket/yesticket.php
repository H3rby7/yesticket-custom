<?php
/**
* Plugin Name: YesTicket
* Plugin URI: ?page=yesticket-plugin
* Version: 2.0.0
* Author: YesTicket
* Author URI: https://www.yesticket.org/
* Description: Onlineticketing
* License: GPL2
* Text Domain: yesticket
*/

include_once "yesticket_plugin_page.php";
include_once "shortcodes/yesticket_events.php";
include_once "shortcodes/yesticket_events_list.php";
include_once "shortcodes/yesticket_events_cards.php";
include_once "shortcodes/yesticket_testimonials.php";

function yesticket_styles()
{
    wp_enqueue_style('yesticket', plugins_url('front.css', __FILE__), false, 'all');
    // wp_enqueue_script('yesticket', plugins_url('front.js', __FILE__));
}

add_action('wp_enqueue_scripts', 'yesticket_styles');
?>