<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

// $type could be "Events" or "Testimonials"
function ytp_render_optionType($type) {?>
    <h4>Type</h4>
    <?php if ($type === 'events') { ?>
      <p class='ml-3'><?php echo __('option events with type explanation', 'yesticket');?></p>
    <?php } elseif ($type === 'testimonials') { ?>
      <p class='ml-3'><?php echo __('option testimonials with type explanation', 'yesticket');?></p>
    <?php } else  {
      throw new AssertionError('Expect argument "$type" to be either "events" or "testimonials"!');
    }?>
    <p class="ml-3"><span class="yt-code">type="performance"</span> <?php echo __('option type chosing "performance"', 'yesticket');?><br>
    <span class="yt-code">type="workshop"</span> <?php echo __('option type chosing "workshop"', 'yesticket');?><br>
    <span class="yt-code">type="festivals"</span> <?php echo __('option type chosing "festivals"', 'yesticket');?><br>
    <span class="yt-code">type="all"</span> <?php echo __('option type chosing "all"', 'yesticket');?></p>
<?php } 
function ytp_render_optionTheme() {?>
    <h4>Theme</h4>
    <p class='ml-3'><?php echo __('option theme explanation', 'yesticket');?></p>
    <p class="ml-3"><span class="yt-code">theme="light"</span> <?php echo __('option theme chosing "light"', 'yesticket');?></p>
    <p class="ml-3"><span class="yt-code">theme="dark"</span> <?php echo __('option theme chosing "dark"', 'yesticket');?></p>
    <p class="ml-3"><span class="yt-code">theme=""</span> <?php echo __('option theme leaving empty', 'yesticket');?></p>
<?php }
function ytp_render_optionCount() {?>
  <h4>Count</h4>
  <p class='ml-3'><?php echo __('option count explanation', 'yesticket');?></p>
  <p class="ml-3"><span class="yt-code">count="6"</span> <?php echo __('option count example', 'yesticket');?></p>
  <p><?php echo __('option count hint', 'yesticket');?></p><?php
}