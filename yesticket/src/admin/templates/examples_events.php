<h2><?php _e("Shortcodes for your events as interactive list.", "yesticket"); ?></h2>
<p><?php _e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_events type="all" count="3"]</span></p>
<h3><?php _e("Options for event shortcodes", "yesticket"); ?></h3>
<h4><?php _e("Details", "yesticket"); ?></h4>
<p><?php _e("Using <b>details</b> you can include the description of your YesTicket event. The description is collapsed and can be expanded.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">details="yes"</span>
  <?php
  /* translators: The sentence actually starts with a non-translatable codeblock 'details="yes"'*/
  _e("will show a link to expand the details.", "yesticket"); ?></p>
<?php
$this->render_template('shortcode_options_type', array("type" => "events"));
$this->render_template('shortcode_options_count');
$this->render_template('shortcode_options_grep');
$this->render_template('shortcode_options_theme');
