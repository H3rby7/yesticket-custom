<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

function ytp_render_no_events() {
  /* translators: When no upcoming events can be found. */
  return '<p>'.__("At this time no upcoming events are available.", "yesticket").'</p>';
}

function ytp_render_no_testimonials() {
  /* translators: When no audience feedback can be found. */
  return '<p>'.__("At this time no audience feedback is present.", "yesticket").'</p>';
}

function ytp_render_eventType($type) {
  if (strcasecmp('auftritt', $type) === 0) {
    /* translators: Event Type 'Performance' */
    return __("Performance", "yesticket");
  }
  if ((strcasecmp('workshop', $type) === 0) or (strcasecmp('kurs', $type) === 0)) {
    /* translators: Event Type 'Workshop' */
    return __("Workshop", "yesticket");
  }
  if (strcasecmp('festival', $type) === 0) {
    /* translators: Event Type 'Festival' */
    return __("Festival", "yesticket");
  }
  return __($type, 'yesticket');
}

function ytp_to_local_datetime($datetimestring) {
  return new DateTime($datetimestring, wp_timezone());
}

function ytp_render_date_and_time($datetimestring) {
  $date = ytp_to_local_datetime($datetimestring);
  /* translators: date format when using date and time, see http://php.net/date */
  $format = __("F j, Y \a\\t g:i A", "yesticket");
  return wp_date($format, $date->getTimestamp());
}

function ytp_render_date($datetimestring) {
  $date = ytp_to_local_datetime($datetimestring);
  /* translators: date format when using only the date, see http://php.net/date */
  $format = __("F j, Y", "yesticket");
  return wp_date($format, $date->getTimestamp());
}