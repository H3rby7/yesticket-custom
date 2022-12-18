<?php

add_option('yesticket_transient_keys', array());

function getDataCached($get_url) {
  $options = get_option('yesticket_settings');
  $CACHE_TIME_IN_MINUTES = $options['cache_time_in_minutes'];
  $CACHE_KEY = ytp_cacheKey($get_url);

  // check if we have cached information
  $data = get_transient($CACHE_KEY);
  if( false === $data ) {
      // Cache not present, we make the API call
      $data = getData($get_url);
      set_transient($CACHE_KEY, $data, $CACHE_TIME_IN_MINUTES * MINUTE_IN_SECONDS );
      // save cache key to options, so we can delete the transient, if necessary
      ytp_addCacheKeyToOptions($CACHE_KEY);
  }
  // at this time we have our data, either from cache or after an API call.
  return $data;
}

function getData($get_url) {
  if (function_exists('curl_version')) {
      $ch = curl_init();
      $timeout = 4;
      curl_setopt($ch, CURLOPT_URL, $get_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      $get_content = curl_exec($ch);
      curl_close($ch);
  } elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
      ini_set('default_socket_timeout', 4);
      $ctx = stream_context_create(array('http'=>
      array(
      'timeout' => 4,  //5 seconds
      )
      ));
      $get_content = file_get_contents($get_url, 0, $ctx);
  } else {
      throw new Exception('We require "cURL" or "allow_url_fopen". Please contact your web hosting provider to install/activate one of the features.');
  }
  if (empty($get_content) && file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
      // in Case of a CURL-error
      ini_set('default_socket_timeout', 4);
      $ctx = stream_context_create(array('http'=>
          array(
          'timeout' => 4,  //5 seconds
          )
          ));
      $get_content = file_get_contents($get_url, 0, $ctx);
  }
  if (empty($get_content)) {
      throw new RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", 'yesticket'));
  }
  $result = json_decode($get_content);
  //return(json_last_error());
  return $result;
}

function validateArguments($att, $options) {
  // We prefer people setting their private info in the settings, rather than the shortcode.
  if (empty($options["organizer_id"]) and empty($att["organizer"])) {
      throw new InvalidArgumentException(__("Please configure your 'organizer-id' in the plugin settings.", 'yesticket'));
  }
  if (empty($options["api_key"]) and empty($att["key"])) {
      throw new InvalidArgumentException(__("Please configure your 'key' in the plugin settings.", 'yesticket'));
  }
  if (!empty($att["type"]) and $att["type"]!="all" and $att["type"]!="performance" and $att["type"]!="Workshop" and $att["type"]!="festival") {
      throw new InvalidArgumentException(__("Please provide a valid 'type'. If you omit the attribute it will default to 'all'. Possible options are 'all', 'performance', 'workshop' and 'festival'.", 'yesticket'));
  }
  return true;
}

function getEventsFromApi($att) {
  return getDataCached(validateAndBuildApiCall($att, "v2/events.php"));
}

function getTestimonialsFromApi($att) {
    return getDataCached(validateAndBuildApiCall($att, "v2/testimonials.php"));
}

function validateAndBuildApiCall($att, $apiEndpoint) {
    $env_add = "";
    if ($att["env"] == 'dev') {
        $env_add = "/dev";
    }
    $options = get_option('yesticket_settings');
    validateArguments($att, $options);
    $get_url = 'https://www.yesticket.org' . $env_add . '/api/' . $apiEndpoint;
    $get_url .= buildYesticketQueryParams($att, $options);
    return $get_url;
}

function buildYesticketQueryParams($att, $options) {
  $queryParams = '';
  if (!empty($att["organizer"])) {
      $queryParams .= '?organizer='.$att["organizer"];
  } else {
      $queryParams .= '?organizer='.$options["organizer_id"];
  }
  if (!empty($att["key"])) {
      $queryParams .= '&key='.$att["key"];
  } else {
      $queryParams .= '&key='.$options["api_key"];
  }
  if (!empty($att["count"])) {
      $queryParams .= '&count='.$att["count"];
  }
  if (!empty($att["type"])) {
      $queryParams .= '&type='.$att["type"];
  }
  return $queryParams;
}
?>