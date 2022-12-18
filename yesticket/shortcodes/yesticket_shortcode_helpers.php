<?php 

include_once(__DIR__ ."/../yesticket_helpers.php");

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
