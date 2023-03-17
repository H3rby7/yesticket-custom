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
    return (\is_array($var) || $var instanceof Countable);
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
  return \plugins_url('yesticket/src/img/' . $fileName);
}

/**
 * Log output from YesTicket plugin
 * 
 * @param string calling $file 
 * @param string calling $line 
 * @param string $log content to be logged
 * 
 */
function ytp_info($file, $line, $log)
{
  $file = \preg_replace('/.*yesticket\/src/', '[YESTICKET]', $file);
  $prefix = "$file@$line: ";
  if (is_array($log) || is_object($log)) {
    \error_log($prefix . \print_r($log, true));
  } else {
    \error_log($prefix . $log);
  }
}

/**
 * Log output from YesTicket plugin if WP_DEBUG is true.
 * 
 * @param string calling $file 
 * @param string calling $line 
 * @param string $log content to be logged
 * 
 */
function ytp_debug($file, $line, $log)
{
  if (true === WP_DEBUG) {
    \ytp_info($file, $line, $log);
  }
}


/**
 * Return html for "no events available"
 */
function ytp_render_no_events()
{
  /* translators: When no upcoming events can be found. */
  return '<p>' . __("At this time no upcoming events are available.", "yesticket") . '</p>';
}

/**
 * Return html for "no testimonials available"
 */
function ytp_render_no_testimonials()
{
  /* translators: When no audience feedback can be found. */
  return '<p>' . __("At this time no audience feedback is present.", "yesticket") . '</p>';
}

/**
 * Print event type localized.
 * (Workaround to make the event $type translatable)
 * 
 * @param string $type of the event
 * 
 */
function ytp_render_eventType($type)
{
  if (\strcasecmp('auftritt', $type) === 0) {
    /* translators: Event Type 'Performance' */
    return _e("Performance", "yesticket");
  }
  if ((\strcasecmp('workshop', $type) === 0) or (\strcasecmp('kurs', $type) === 0)) {
    /* translators: Event Type 'Workshop' */
    return _e("Workshop", "yesticket");
  }
  if (\strcasecmp('festival', $type) === 0) {
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
function ytp_to_local_datetime($datetimestring)
{
  return new DateTime($datetimestring, \wp_timezone());
}

/**
 * Print date and time in localized format
 * 
 * @param string $datetimestring
 */
function ytp_render_date_and_time($datetimestring)
{
  $date = \ytp_to_local_datetime($datetimestring);
  /* translators: date format when using date and time, see http://php.net/date */
  $format = __("F j, Y \a\\t g:i A", "yesticket");
  echo \wp_date($format, $date->getTimestamp());
}

/**
 * Render date in localized format
 * 
 * @param string $datetimestring
 * 
 * @return string
 */
function ytp_render_date($datetimestring)
{
  $date = \ytp_to_local_datetime($datetimestring);
  /* translators: date format when using only the date, see http://php.net/date */
  $format = __("F j, Y", "yesticket");
  return \wp_date($format, $date->getTimestamp());
}

/**
 * Render time in localized format
 * 
 * @param string $datetimestring
 * 
 * @return string
 */
function ytp_render_time($datetimestring)
{
  $date = \ytp_to_local_datetime($datetimestring);
  /* translators: time format when using only the time, see http://php.net/date */
  $format = __("g:i A", "yesticket");
  return \wp_date($format, $date->getTimestamp());
}

/**
 * Return div with theme and shortcode class.
 * Remember to close the <div> later.
 * 
 * @param string $datetimestring
 * 
 * @return string <div>
 */
function ytp_render_shortcode_container_div($shortcode_class, $att)
{
  if (!isset($att["theme"])) {
    return "<div class='$shortcode_class ytp-default'>\n";
  }
  if ($att["theme"] == "light") {
    return "<div class='$shortcode_class ytp-light'>\n";
  } elseif ($att["theme"] == "dark") {
    return "<div class='$shortcode_class ytp-dark'>\n";
  }
  return "<div class='$shortcode_class " . $att["theme"] . "'>\n";
}

/**
 * Find the last occurence of a regex within a string
 * 
 * @param string $haystack
 * @param string $needlePattern
 * 
 * @return integer|false starting position of last occurence or FALSE if none was found.
 */
function strpos_findLast_viaRegex($haystack, $needlePattern)
{
  // https://www.php.net/manual/de/function.preg-match-all.php
  \preg_match_all($needlePattern, $haystack, $findings, PREG_OFFSET_CAPTURE);
  // $findings is an array containing the findings
  // each match is an array of size 1, containing at [0] an array of 
  // [0] being the match and [1] the corresponding index.
  $highestIndex = -1;
  foreach ($findings as $pattern) {
    foreach ($pattern as $match) {
      if ($match[1] > $highestIndex) {
        $highestIndex = $match[1];
      }
    }
  }
  return $highestIndex > -1 ? $highestIndex : false;
}

/**
 * Send out the headers defined in $response, if headers have not been sent.
 * TODO: maybe '@runInSeparateProcess' helps to test headers?
 * 
 * @param \WP_REST_Response $response
 */
function ytp_sendHeaders($response)
{
  if (!\headers_sent()) {
    foreach ($response->get_headers() as $header => $value) {
      \header("${header}: ${value}", true);
    }
  }
}
