<?php

add_option('yesticket_transient_keys', array());

function ytp_api_getDataCached($get_url) {
  $options = get_option('yesticket_settings');
  $CACHE_TIME_IN_MINUTES = $options['cache_time_in_minutes'];
  $CACHE_KEY = ytp_cacheKey($get_url);

  // check if we have cached information
  $data = get_transient($CACHE_KEY);
  if( false === $data ) {
      // Cache not present, we make the API call
      $data = ytp_api_getData($get_url);
      set_transient($CACHE_KEY, $data, $CACHE_TIME_IN_MINUTES * MINUTE_IN_SECONDS );
      // save cache key to options, so we can delete the transient, if necessary
      ytp_addCacheKeyToOptions($CACHE_KEY);
  }
  // at this time we have our data, either from cache or after an API call.
  return $data;
}

function ytp_api_getData($get_url) {
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
      throw new RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", "yesticket"));
  }
  $result = json_decode($get_content);
  //return(json_last_error());
  return $result;
}

function ytp_api_validateArguments($att, $options) {
  // We prefer people setting their private info in the settings, rather than the shortcode.
  if (empty($options["organizer_id"]) and empty($att["organizer"])) {
      throw new InvalidArgumentException(
        /* translators: Error message, if the plugin is not properly configured*/
        __("Please configure your 'organizer-id' in the plugin settings.", "yesticket")
    );
  }
  if (empty($options["api_key"]) and empty($att["key"])) {
      throw new InvalidArgumentException(
        /* translators: Error message, if the plugin is not properly configured*/
        __("Please configure your 'key' in the plugin settings.", "yesticket")
    );
  }
  if (!empty($att["type"]) and $att["type"]!="all" and $att["type"]!="performance" and $att["type"]!="Workshop" and $att["type"]!="festival") {
      throw new InvalidArgumentException(
        /* translators: Error message, if the shortcode uses wrong/invalid types*/
        __("Please provide a valid 'type'. If you omit the attribute it will default to 'all'. Possible options are 'all', 'performance', 'workshop' and 'festival'.", "yesticket")
    );
  }
  return true;
}

function ytp_api_getEvents($att) {
  return ytp_api_getDataCached(ytp_api_validateAndBuildApiUrl($att, "v2/events.php"));
}

function ytp_api_getTestimonials($att) {
    return ytp_api_getDataCached(ytp_api_validateAndBuildApiUrl($att, "v2/testimonials.php"));
}

function ytp_api_validateAndBuildApiUrl($att, $apiEndpoint) {
    $env_add = "";
    if ($att["env"] == 'dev') {
        $env_add = "/dev";
    }
    $options = get_option('yesticket_settings');
    ytp_api_validateArguments($att, $options);
    $get_url = 'https://www.yesticket.org' . $env_add . '/api/' . $apiEndpoint;
    $get_url .= ytp_api_buildQueryParams($att, $options);
    return $get_url;
}

function ytp_api_buildQueryParams($att, $options) {
    $queryParams = '';
    if (!empty($att["count"])) {
        $queryParams .= '&count='.$att["count"];
    }
    if (!empty($att["type"])) {
        $queryParams .= '&type='.$att["type"];
    }
    $lang = get_locale();
    $langUnderscorePos = strpos($lang, "_");
    if ($langUnderscorePos != false and $langUnderscorePos > -1) {
        $lang = substr($lang, 0, $langUnderscorePos);
    }
    $queryParams .= '&lang='.$lang;
    ytp_log(__FILE__."@".__LINE__.": 'Public query params for API Call: " . $queryParams."'");
    // We keep organizedID and key out of the ytp_log.
    $secretQueryParams = '';
    if (!empty($att["organizer"])) {
        $secretQueryParams .= '?organizer='.$att["organizer"];
    } else {
        $secretQueryParams .= '?organizer='.$options["organizer_id"];
    }
    if (!empty($att["key"])) {
        $secretQueryParams .= '&key='.$att["key"];
    } else {
        $secretQueryParams .= '&key='.$options["api_key"];
    }
    return $secretQueryParams.$queryParams;
}
?>