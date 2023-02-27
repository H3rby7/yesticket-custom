<div class='ytp-event-row' id="<?php $this->render_event_id($item); ?>">
  <div class='ytp-event-info'>
    <h3 class='ytp-event-name'>
      <?php echo \htmlentities($item->event_name); ?>
      <?php if ($att["type"] == "all") { ?>
        <span class='ytp-event-type'><?php \ytp_render_eventType($item->event_type); ?></span>
      <?php } ?>
    </h3>
    <span class='ytp-event-location'><?php echo \htmlentities($item->location_name); ?></span>
    <span class='ytp-event-city'><?php echo \htmlentities($item->location_city); ?></span>
    <span class='ytp-event-date'><?php \ytp_render_date_and_time($item->event_datetime); ?></span>
    <?php if (!empty($item->event_urgency_string)) { ?>
      <span class='ytp-event-urgency'><?php echo \htmlentities($item->event_urgency_string); ?></span>
    <?php } ?>
    <?php if ($att["details"] == "yes") { ?>
      <div class='ytp-event-details'>
        <?php echo $this->render_template('event_details', \compact("item")); ?>
      </div>
    <?php } ?>
  </div>
  <div class='ytp-event-ticket'>
    <a href="<?php echo $item->yesticket_booking_url; ?>" target="_blank" class="ytp-button">
      <?php _e("Tickets", "yesticket"); ?>
    </a>
  </div>
</div>