<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

/**
 * Return html for "no events available"
 */
function ytp_render_no_events() {
  /* translators: When no upcoming events can be found. */
  return '<p>'.__("At this time no upcoming events are available.", "yesticket").'</p>';
}

/**
 * Return html for "no testimonials available"
 */
function ytp_render_no_testimonials() {
  /* translators: When no audience feedback can be found. */
  return '<p>'.__("At this time no audience feedback is present.", "yesticket").'</p>';
}

/**
 * Workaround to make the event $type available
 * 
 * @param string $type of the event
 * 
 * @return string the translated event type or the input
 */
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

/**
 * Create date using the wp timezone
 * 
 * @param string $datetimestring
 * 
 * @return DateTime
 */
function ytp_to_local_datetime($datetimestring) {
  return new DateTime($datetimestring, wp_timezone());
}

/**
 * Render date and time in localized format
 * 
 * @param string $datetimestring
 * 
 * @return string
 */
function ytp_render_date_and_time($datetimestring) {
  $date = ytp_to_local_datetime($datetimestring);
  /* translators: date format when using date and time, see http://php.net/date */
  $format = __("F j, Y \a\\t g:i A", "yesticket");
  return wp_date($format, $date->getTimestamp());
}

/**
 * Render date in localized format
 * 
 * @param string $datetimestring
 * 
 * @return string
 */
function ytp_render_date($datetimestring) {
  $date = ytp_to_local_datetime($datetimestring);
  /* translators: date format when using only the date, see http://php.net/date */
  $format = __("F j, Y", "yesticket");
  return wp_date($format, $date->getTimestamp());
}

/**
 * Render time in localized format
 * 
 * @param string $datetimestring
 * 
 * @return string
 */
function ytp_render_time($datetimestring) {
  $date = ytp_to_local_datetime($datetimestring);
  /* translators: time format when using only the time, see http://php.net/date */
  $format = __("g:i A", "yesticket");
  return wp_date($format, $date->getTimestamp());
}

/**
 * Return div with theme and shortcode class.
 * Remember to close the <div> later.
 * 
 * @param string $datetimestring
 * 
 * @return string <div>
 */
function ytp_render_shortcode_container_div($shortcode_class, $att) {
  if ($att["theme"] == "light") {
    return "<div class='$shortcode_class ytp-light'>\n";
  } elseif ($att["theme"] == "dark") {
      return "<div class='$shortcode_class ytp-dark'>\n";
  }
  return "<div class='$shortcode_class ytp-default ".$att["theme"]."'>\n";
}
