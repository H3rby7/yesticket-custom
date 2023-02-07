<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

// $type could be "Events" or "Testimonials"
function ytp_render_optionType($type) {?>
    <h4>Type</h4>
    <?php if ($type === 'events') { ?>
      <p class='ml-3'><?php echo __("Using <b>type</b> you can filter your events by type.", "yesticket");?></p>
    <?php } elseif ($type === 'testimonials') { ?>
      <p class='ml-3'><?php echo __("Using <b>type</b> you can filter your testimonials by type.", "yesticket");?></p>
    <?php } else  {
      throw new AssertionError('Expect argument "$type" to be either "events" or "testimonials"!');
    }?>
    <p class="ml-3"><span class="ytp-code">type="performance"</span><?php
    /* translators: Explanation of using the shortcode option 'type="performance"'*/
    echo __("only shows/performances", "yesticket");?><br>
    <span class="ytp-code">type="workshop"</span> <?php 
    /* translators: Explanation of using the shortcode option 'type="workshop"'*/
    echo __("only workshops", "yesticket");?><br>
    <span class="ytp-code">type="festivals"</span> <?php 
    /* translators: Explanation of using the shortcode option 'type="festivals"'*/
    echo __("only festivals", "yesticket");?><br>
    <span class="ytp-code">type="all"</span> <?php 
    /* translators: Explanation of using the shortcode option 'type="all"'*/
    echo __("Everything, mixed", "yesticket");?></p>
<?php } 
function ytp_render_optionTheme() {?>
    <h4>Theme</h4>
    <p class='ml-3'><?php echo __("Buttons will be in a light grey and match lighter backgrounds", "yesticket");?></p>
    <p class="ml-3"><span class="ytp-code">theme="light"</span> <?php echo __("Buttons will be in a light grey and match lighter backgrounds", "yesticket");?></p>
    <p class="ml-3"><span class="ytp-code">theme="dark"</span> <?php echo __("Buttons will be in a dark grey and match darker backgrounds", "yesticket");?></p>
    <p class="ml-3"><span class="ytp-code">theme=""</span> <?php echo __("If you do not provide a theme only basic formatting is applied. This is an option to provide your own clean CSS. Maybe you are a Webdesigner after all?", "yesticket");?></p>
<?php }
function ytp_render_optionCount() {?>
  <h4>Count</h4>
  <p class='ml-3'><?php echo __("Using <b>count</b> you can define the maximum amount of elements.", "yesticket");?></p>
  <p class="ml-3"><span class="ytp-code">count="6"</span> <?php 
    /* translators: The sentence actually starts with a non-translatable codeblock 'count="6"'*/
  echo __("a maximum of 6 events is displayed", "yesticket");?></p>
  <p class="ml-3"><?php 
  /* translators: Note, when using the shortcode option 'count'*/
  echo __("Please note, that count describes an upper limit. If fewer items are available, only these can be displayed.", "yesticket");
  ?></p><?php
}