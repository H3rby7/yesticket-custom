<?php

namespace YesTicket;

use YesTicket\Cache;

include_once("cache.php");
include_once("functions.php");
include_once("plugin_options.php");
/**
 * Cache for YesTicket API Calls
 */
class RestCache extends Cache
{
  static protected $instance;

  /**
   * @return RestCache $instance
   */
  static public function getInstance()
  {
    if (!isset(RestCache::$instance)) {
      RestCache::$instance = new RestCache();
    }
    return RestCache::$instance;
  }

  /**
   * Get data from the specified $get_url. 
   * Use cached response, if present, else we make a new call and sve the data to cache
   * 
   * @param string $get_url the full api call URL
   * 
   * @return mixed Response.
   */
  public function getFromCacheOrFresh($get_url)
  {
    $CACHE_KEY = $this->cacheKey($get_url);
    // check if we have cached information
    $data = get_transient($CACHE_KEY);
    if (false === $data) {
      // Cache not present, we make the API call
      $data = $this->getData($get_url);
      $this->cache($CACHE_KEY, $data);
    }
    // at this time we have our data, either from cache or after an API call.
    return $data;
  }

  /**
   * Get data from the specified $get_url. 
   * 
   * @param string $get_url the full api call URL
   * 
   * @return string Response body
   */
  protected function getData($get_url)
  {
    $this->logRequestMasked($get_url);
    $http = new \WP_Http;
    $result = $http->get($get_url);
    if (\is_wp_error($result)) {
      $code = $result->get_error_code();
      if (\is_string($code)) {
        throw new \RuntimeException($result->get_error_message() . ' ' . $code);
      }
      throw new \RuntimeException($result->get_error_message(), $code);
    }
    if (empty($result['body']) || $result['response']['code'] != 200) {
      throw new \RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", "yesticket"));
    }
    return $result['body'];
  }
}
