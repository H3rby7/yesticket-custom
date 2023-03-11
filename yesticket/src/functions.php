<?php

include_once("admin/plugin_menu.php");
include_once("helpers/functions.php");
include_once("rest/image_endpoint.php");
include_once("shortcodes/yesticket_events.php");
include_once("shortcodes/yesticket_events_list.php");
include_once("shortcodes/yesticket_events_cards.php");
include_once("shortcodes/yesticket_testimonials.php");
include_once("shortcodes/yesticket_slides.php");

\add_action('init', 'ytp_init_callback');

function ytp_init_callback()
{
  $pathToSiteCss = \plugins_url('ytp-site.css', __FILE__);
  $pathToAdminCss = \plugins_url('admin/styles.css', __FILE__);
  $pathToLanguages = 'yesticket/src/languages/';
  \ytp_debug(__FILE__, __LINE__, "Loading YesTicket plugin ...");
  if (!\wp_register_style('yesticket', $pathToSiteCss, false, 'all')) {
    \ytp_info(__FILE__, __LINE__, "Could not register_style: 'yesticket' from '$pathToSiteCss'.");
  }
  if (!\wp_register_style('yesticket-admin', $pathToAdminCss, false, 'all')) {
    \ytp_info(__FILE__, __LINE__, "Could not register_style: 'yesticket-admin' from '$pathToAdminCss'.");
  }
  \YesTicket\Slides::registerFiles();
  if (!\load_plugin_textdomain('yesticket', false, $pathToLanguages) && true === WP_DEBUG) {
    $locale = get_locale();
    \ytp_debug(__FILE__, __LINE__, "Could not load 'yesticket' translations for $locale from '$pathToLanguages'. Falling back to 'en'.");
  }
  \ytp_debug(__FILE__, __LINE__, "YesTicket plugin loaded ...");
}
