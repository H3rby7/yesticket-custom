<h2>
  <?php _e("Shortcodes for your events as slides.", "yesticket"); ?>
</h2>
<p>
  <?php _e("quickstart", "yesticket"); ?>: <span class="ytp-code">[yesticket_slides]</span>
</p>
<p>
  <?php _e("Beware: This shortcode must be placed on a separate page. Visit that page and enter fullscreen mode to view your presentation.", "yesticket"); ?>
</p>
<p>
  <?php _e("Running this presentation on a big screen, before/after a show, is a nice way to inform your audience about upcoming events.", "yesticket"); ?>
</p>
<h3>
  <?php _e("Options for slideshow shortcodes", "yesticket"); ?>
</h3>

<h4><?php _e("Welcome Slide", "yesticket"); ?></h4>
<p class='ml-3'>
  <?php _e("Using 'welcome-1', 'welcome-2' and 'welcome-3', you can adjust the text on the welcome splash slide.", "yesticket"); ?></p>
<p class="ml-3">
  <span class="ytp-code">
    welcome-1="<?php _e('welcome to our', "yesticket"); ?>"
    welcome-2="<?php _e('improv theatre show', "yesticket"); ?>"
    welcome-3="<?php _e('where everything is made up', "yesticket"); ?>"
  </span></br>
  <?php _e("'welcome-2' defines the bigger text in the center.", "yesticket"); ?>
</p>

<h4><?php _e("Color One", "yesticket"); ?></h4>
<p class='ml-3'>
  <?php _e("'color-1' is used for the background of the welcome slide and as text color on other slides.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">color-1="#fff000"</span></p>
<p class="ml-3">
  <?php _e("Any valid css color definiton is okay. Use this option to bring in your company's style.", "yesticket"); ?>
</p>
<h4><?php _e("Color Two", "yesticket"); ?></h4>
<p class='ml-3'>
  <?php _e("'color-2' is used for the text color of the welcome slide and as box shadow color on other slides.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">color-2="rgb(0, 80, 80)"</span></p>
<p class="ml-3">
  <?php _e("Any valid css color definiton is okay. Use this option to bring in your company's style.", "yesticket"); ?>
</p>

<h4><?php _e("Ms-Per-Slide", "yesticket"); ?></h4>
<p class='ml-3'>
  <?php _e("Using 'ms-per-slide', you adjust the duration of each slide. The unit is milliseconds.", "yesticket"); ?></p>
<p class="ml-3"><span class="ytp-code">ms-per-slide="8000"</span>
  <?php
  /* translators: The sentence actually starts with a non-translatable codeblock 'ms-per-slide="8000"'*/
  _e("will display each slide for 8 seconds.", "yesticket"); ?></p>

<h4><?php _e("Teaser-Length", "yesticket"); ?></h4>
<p class='ml-3'>
  <?php _e("Using 'teaser-length', you can define the maximum characters of the descriptive text. The text is cut at the end of the last sentence that fits within the limit.", "yesticket"); ?>
</p>
<p class="ml-3"><span class="ytp-code">teaser-length="123"</span></p>

<h4><?php _e("Text-Scale", "yesticket"); ?></h4>
<p class='ml-3'>
  <?php _e("Using 'text-scale', you can change the font-size of the presentation.", "yesticket"); ?>
</p>
<p class="ml-3">
  <span class="ytp-code">text-scale="120%"</span>
</p>
<p class='ml-3'>
  <?php _e("Check how small/big you need this value to be, so your audience can read the information well.", "yesticket"); ?>
</p>
<?php

$this->render_template('shortcode_options_type', array("type" => "events"));
$this->render_template('shortcode_options_count');
$this->render_template('shortcode_options_grep');
