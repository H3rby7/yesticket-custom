<section class="yesticket-slide">
  <span class="background fadeIn" style="background-image:url('<?php echo $event->event_picture_url; ?>')"></span>
  <div class="wrap">
    <div class="yesticket-event-meta slide-top slideInLeft delay">
      <h2 class="yesticket-event-name"><?php echo $event->event_name; ?></h2>
      <p><?php \ytp_render_date_and_time($event->event_datetime); ?>,
        <?php echo $event->location_name; ?></p>
      <div class="backdrop-dark">
        <div></div>
      </div>
    </div>
    <div class="yesticket-event-teaser slideInRight delay delay2">
      <p><?php $this->eventDescription($event, $att); ?></p>
      <div class="backdrop-dark">
        <div></div>
      </div>
    </div>
  </div>
</section>