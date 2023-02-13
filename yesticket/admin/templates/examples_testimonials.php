<h2><?php _e("Shortcodes for your testimonials.", "yesticket"); ?></h2>
<p><?php _e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_testimonials count="30"]</span></p>
<h3><?php _e("Options for testimonial shortcodes", "yesticket"); ?></h3>
<h4>Details</h4>
<p><?php _e("Using details you can display the corresponding event to a testimonial.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">details="yes"</span>
  <?php
  /* translators: The sentence actually starts with a non-translatable codeblock 'details="yes"'*/
  _e("will add the event name to each testimonial, if present.", "yesticket"); ?></p>
<?php
$this->render_optionType('testimonials');
$this->render_optionCount();
