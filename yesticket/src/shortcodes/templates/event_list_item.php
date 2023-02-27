<li class='ytp-event-list-row'>
  <ul>
    <li class='ytp-event-list-date'><?php echo \ytp_render_date($item->event_datetime); ?></li>
    <li class='ytp-event-list-time'><?php echo \ytp_render_time($item->event_datetime); ?></li>
    <?php if ($att["type"] == "all") { ?>
      <li class='ytp-event-list-type'><?php \ytp_render_eventType($item->event_type) ?></li>
    <?php } ?>
    <li class='ytp-event-list-name'><?php echo \htmlentities($item->event_name); ?></li>
    <li class='ytp-event-list-location'><?php echo \htmlentities($item->location_name); ?></li>
    <li class='ytp-event-list-city'><?php echo \htmlentities($item->location_city); ?></li>
    <?php if ($att["ticketlink"] == "yes") { ?>
      <li class="ytp-event-list-tickets">
        <a href="<?php echo $item->yesticket_booking_url; ?>" target="_blank">
          <?php \_e("Tickets", "yesticket"); ?>
        </a>
      </li>
    <?php } ?>
  </ul>
</li>