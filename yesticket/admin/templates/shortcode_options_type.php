<h4><?php _e("Type", "yesticket");?></h4>
<?php
if ($type === 'events') {
?>
  <p>
    <?php _e("Using <b>type</b> you can filter your events by type.", "yesticket"); ?>
  </p>
<?php } elseif ($type === 'testimonials') { ?>
  <p>
    <?php _e("Using <b>type</b> you can filter your testimonials by type.", "yesticket"); ?>
  </p>
<?php } else {
  // Decided against a translation since this is an error for programmers editing this source code.
  throw new AssertionError('Expect argument "$type" to be either "events" or "testimonials"!');
} ?>
<p class="ml-3">
  <span class="ytp-code">type="performance"</span>
  <?php
  /* translators: Explanation of using the shortcode option 'type="performance"'*/
  _e("only shows/performances", "yesticket"); ?><br>
  <span class="ytp-code">type="workshop"</span>
  <?php
  /* translators: Explanation of using the shortcode option 'type="workshop"'*/
  _e("only workshops", "yesticket"); ?><br>
  <span class="ytp-code">type="festivals"</span>
  <?php
  /* translators: Explanation of using the shortcode option 'type="festivals"'*/
  _e("only festivals", "yesticket"); ?><br>
  <span class="ytp-code">type="all"</span>
  <?php
  /* translators: Explanation of using the shortcode option 'type="all"'*/
  _e("Everything, mixed", "yesticket"); ?>
</p>