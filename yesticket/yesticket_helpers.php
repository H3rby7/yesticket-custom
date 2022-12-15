<?php
if (!function_exists('is_countable')) {
  function is_countable($var)
  {
      return (is_array($var) || $var instanceof Countable);
  }
}

function ytp_cacheKey($get_url) {
  // common key specific to yesticket to set and retrieve WP_TRANSIENTS
  return 'yesticket_' . md5($get_url);
}

function ytp_addCacheKeyToOptions($CACHE_KEY) {
  $cacheKeys = get_option('yesticket_transient_keys', array());
  if (!in_array($CACHE_KEY, $cacheKeys)) {
      // unknown cache key, add to known keys
      $cacheKeys[] = $CACHE_KEY;
      update_option('yesticket_transient_keys', $cacheKeys);
  }
}

function ytp_getImageUrl($fileName) {
  return plugin_dir_url(__FILE__).'img/'.$fileName;
}
?>