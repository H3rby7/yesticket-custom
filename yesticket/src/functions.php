<?php

include_once("admin/plugin_menu.php");
include_once("rest/image_endpoint.php");
include_once("shortcodes/yesticket_events.php");
include_once("shortcodes/yesticket_events_list.php");
include_once("shortcodes/yesticket_events_cards.php");
include_once("shortcodes/yesticket_testimonials.php");

\add_action('init', 'ytp_init_callback');

function ytp_init_callback()
{
  $pathToSiteCss = \plugins_url('ytp-site.css', __FILE__);
  $pathToAdminCss = \plugins_url('admin/styles.css', __FILE__);
  $pathToLanguages = 'yesticket/src/languages/';
  if (true === WP_DEBUG) {
    \error_log("Loading YesTicket plugin ...");
  }
  if (!\wp_register_style('yesticket', $pathToSiteCss, false, 'all')) {
    \error_log("Could not register_style: 'yesticket' from '$pathToSiteCss'.");
  }
  if (!\wp_register_style('yesticket-admin', $pathToAdminCss, false, 'all')) {
    \error_log("Could not register_style: 'yesticket-admin' from '$pathToAdminCss'.");
  }
  if (!\load_plugin_textdomain('yesticket', false, $pathToLanguages)) {
    \error_log("Could not load_plugin_textdomain: 'yesticket' from '$pathToLanguages'.");
  }
  if (true === WP_DEBUG) {
    \error_log("YesTicket plugin loaded ...");
  }
}
