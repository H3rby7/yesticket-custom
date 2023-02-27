<h2><?php \_e("Shortcodes for your events as list.", "yesticket"); ?></h2>
<p><?php \_e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_events_list type="all" count="3"]</span></p>
<h3><?php \_e("Options for event list shortcodes", "yesticket"); ?></h3>
<?php
$this->render_template('shortcode_options_type', array("type" => "events"));
$this->render_template('shortcode_options_count');
$this->render_template('shortcode_options_grep');
