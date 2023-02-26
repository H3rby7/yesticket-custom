<?php
if (!function_exists('is_countable')) {
  // For PHP < 7.3.0
  /**
   * Verify that the contents of a variable is a countable value.
   * 
   * @param mixed $var
   * 
   * @return boolean TRUE if countable; else FALSE
   */
  function is_countable($var)
  {
    return (is_array($var) || $var instanceof Countable);
  }
}

/**
 * Get the URL of an image to be accessed via browser.
 * 
 * @param string $fileName of the image inside this plugin's '/img' directory
 * 
 * @return string the browser-accessible URL
 */
function ytp_getImageUrl($fileName)
{
  return plugin_dir_url(__DIR__) . 'img/' . $fileName;
}

/**
 * Log output from YesTicket plugin if WP_DEBUG is true.
 * 
 * @param string $log content to be logged
 * 
 */
function ytp_log($log)
{
  if (true === WP_DEBUG) {
    if (is_array($log) || is_object($log)) {
      error_log("YESTICKET: " . print_r($log, true));
    } else {
      error_log("YESTICKET: " . $log);
    }
  }
}


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
 * Print event type localized.
 * (Workaround to make the event $type translatable)
 * 
 * @param string $type of the event
 * 
 */
function ytp_render_eventType($type) {
  if (strcasecmp('auftritt', $type) === 0) {
    /* translators: Event Type 'Performance' */
    return _e("Performance", "yesticket");
  }
  if ((strcasecmp('workshop', $type) === 0) or (strcasecmp('kurs', $type) === 0)) {
    /* translators: Event Type 'Workshop' */
    return _e("Workshop", "yesticket");
  }
  if (strcasecmp('festival', $type) === 0) {
    /* translators: Event Type 'Festival' */
    return _e("Festival", "yesticket");
  }
  _e($type, 'yesticket');
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
 * Print date and time in localized format
 * 
 * @param string $datetimestring
 */
function ytp_render_date_and_time($datetimestring) {
  $date = ytp_to_local_datetime($datetimestring);
  /* translators: date format when using date and time, see http://php.net/date */
  $format = __("F j, Y \a\\t g:i A", "yesticket");
  echo wp_date($format, $date->getTimestamp());
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
