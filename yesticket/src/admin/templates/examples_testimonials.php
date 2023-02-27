<h2><?php \_e("Shortcodes for your testimonials.", "yesticket"); ?></h2>
<p><?php \_e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_testimonials count="30"]</span></p>
<h3><?php \_e("Options for testimonial shortcodes", "yesticket"); ?></h3>
<h4><?php \_e("Details", "yesticket"); ?></h4>
<p><?php \_e("Using <b>details</b> you can display the corresponding event to a testimonial.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">details="yes"</span>
  <?php
  /* translators: The sentence actually starts with a non-translatable codeblock 'details="yes"'*/
  \_e("will add the event name to each testimonial, if present.", "yesticket"); ?></p>
<h4><?php \_e("Design", "yesticket"); ?></h4>
<p><?php \_e("Using <b>design</b> you can choose between different preset layouts.", "yesticket"); ?></p>
<p class="ml-3">
  <span class="ytp-code">design="basic"</span>
  <?php
  /* translators: Explanation of using the shortcode option 'design="basic"' for testimonials*/
  \_e("the testimonials are aligned left", "yesticket"); ?><br>
  <span class="ytp-code">design="jump"</span>
  <?php
  /* translators: Explanation of using the shortcode option 'design="jump"' for testimonials*/
  \_e("the testimonials are aligned left/right in turns.", "yesticket"); ?>
</p>
<?php
$this->render_template('shortcode_options_type', array("type" => "testimonials"));
$this->render_template('shortcode_options_count');
$this->render_template('shortcode_options_theme');
