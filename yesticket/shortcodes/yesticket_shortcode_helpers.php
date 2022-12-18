<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

function ytp_render_no_events() {
  return '<p>'.__("At this time no upcoming events are available.", "yesticket").'</p>';
}

function ytp_render_no_testimonials() {
  return '<p>'.__("At this time no audience feedback is present.", "yesticket").'</p>';
}

function ytp_render_eventType($type) {
  if (strcasecmp('auftritt', $type) === 0) {
    return __("Performance", "yesticket");
  }
  if ((strcasecmp('workshop', $type) === 0) or (strcasecmp('kurs', $type) === 0)) {
    return __("Workshop", "yesticket");
  }
  if (strcasecmp('festival', $type) === 0) {
    return __("Festival", "yesticket");
  }
  return __($type, 'yesticket');
} 

function ytc_extract_event_datetime($event) {
  $eventdate = new DateTime($event->event_datetime, wp_timezone());
  return $eventdate;
}

function ytc_render_date_and_time($event) {
  $date = ytc_extract_event_datetime($event);
  $format = __("d.m.y H:i \U\h\\r", "yesticket");
  return wp_date($format, $date->getTimestamp());
}