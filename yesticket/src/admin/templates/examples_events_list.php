<h2><?php _e("Shortcodes for your events as list.", "yesticket"); ?></h2>
<p><?php _e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_events_list type="all" count="3"]</span></p>
<h3><?php _e("Options for event list shortcodes", "yesticket"); ?></h3>
<h4><?php _e("Link to tickets", "yesticket"); ?></h4>
<p><?php _e("Using <b>ticketlink</b> you control whether or not to show a link to the ticket site.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">ticketlink="yes"</span>
  <?php
  /* translators: The sentence actually starts with a non-translatable codeblock 'ticketlink="yes"'*/
  _e("will show the link to your yesticket.org event.", "yesticket"); ?></p>
<?php
$this->render_template('shortcode_options_type', array("type" => "events"));
$this->render_template('shortcode_options_count');
$this->render_template('shortcode_options_grep');
