<h2><?php _e("Shortcodes for your events as cards.", "yesticket"); ?></h2>
<p><?php _e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_events_cards count="30"]</span></p>
<h3><?php _e("Options for event card shortcodes", "yesticket"); ?></h3>
<?php
$this->render_template('shortcode_options_type', array("type" => "events"));
$this->render_template('shortcode_options_count');
$this->render_template('shortcode_options_grep');
$this->render_template('shortcode_options_theme');