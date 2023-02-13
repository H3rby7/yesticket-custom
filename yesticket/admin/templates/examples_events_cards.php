<h2><?php _e("Shortcodes for your events as cards.", "yesticket"); ?></h2>
<p><?php _e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_events_cards count="30"]</span></p>
<h3><?php _e("Options for event card shortcodes", "yesticket"); ?></h3>
<?php
$this->render_template('shortcode_options_type', array("type"=>"events"));
$this->render_template('shortcode_options_count');
$this->render_template('shortcode_options_theme');
?>
<h4><?php _e("Filter", "yesticket"); ?></h4>
<p><?php
    _e("Using <b>grep</b> you can filter your events by their title.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">grep="Johnstone"</span>
  <?php
  /* translators: The sentence actually starts with a non-translatable codeblock 'grep="Johnstone"'*/
  _e("will only display events, who have \"Johnstone\" in their title.", "yesticket"); ?></p>