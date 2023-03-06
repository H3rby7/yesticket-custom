<?php
$time = \ytp_to_local_datetime($item->event_datetime);
$ts = $time->getTimestamp();
?>
<a href="<?php echo $item->yesticket_booking_url; ?>" target="_blank">
  <div class="ytp-event-card">
    <div class="ytp-event-card-image" style="background-image: url('<?php echo $item->getPictureUrl(); ?>')"></div>
    <div class="ytp-event-card-text-wrapper">
      <div class="ytp-event-card-date">
        <span class="ytp-event-card-month"><?php echo \wp_date('M', $ts); ?></span><br>
        <span class="ytp-event-card-day"><?php echo \wp_date('d', $ts); ?></span><br>
        <span class="ytp-event-card-year"><?php echo \wp_date('Y', $ts); ?></span>
      </div>
      <div class="ytp-event-card-body">
        <div class="ytp-event-card-body-fade-out"></div>
        <small class="ytp-event-card-location"><?php echo \htmlentities($item->location_name); ?></small>
        <strong class="ytp-event-card-title"><?php echo \htmlentities($item->event_name); ?></strong>
      </div>
    </div>
  </div>
</a>