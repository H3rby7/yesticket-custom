<?php

namespace YesTicket;

include_once("functions.php");
include_once("plugin_options.php");
/**
 * Cache for YesTicket API Calls
 */
class Cache
{
    /**
     * The $instance
     *
     * @var Cache
     */
    static protected $instance;

    /**
     * Get the $instance
     * 
     * @return Cache $instance
     */
    static public function getInstance()
    {
        if (!isset(Cache::$instance)) {
            Cache::$instance = new Cache();
        }
        Cache::$instance->ensureOptionExists();
        return Cache::$instance;
    }

    /**
     * Make sure the option handling our cache_keys exists and is an array.
     */
    protected function ensureOptionExists()
    {
        $opt = \get_option('yesticket_transient_keys', false);
        if (!$opt || !\is_array($opt)) {
            \update_option('yesticket_transient_keys', array());
        }
    }

    /**
     * Get data from the specified $get_url. 
     * Use cached response, if present, else we make a new call and sve the data to cache
     * 
     * @param string $get_url the full api call URL
     * 
     * @return string Response body.
     */
    public function getFromCacheOrFresh($get_url)
    {
        $CACHE_TIME_IN_MINUTES = PluginOptions::getInstance()->getCacheTimeInMinutes();
        $CACHE_KEY = $this->cacheKey($get_url);

        // check if we have cached information
        $data = get_transient($CACHE_KEY);
        if (false === $data) {
            // Cache not present, we make the API call
            $data = $this->getData($get_url);
            \set_transient($CACHE_KEY, $data, $CACHE_TIME_IN_MINUTES * MINUTE_IN_SECONDS);
            // save cache key to options, so we can delete the transient, if necessary
            $this->addKeyToActiveCaches($CACHE_KEY);
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
            throw new \RuntimeException($result->get_error_message());
        }
        if (empty($result['body']) || $result['response']['code'] != 200) {
            throw new \RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", "yesticket"));
        }
        return $result['body'];
    }

    /**
     * Transform the $get_url into a key used for WP_TRANSIENTS
     * 
     * @param string $get_url the full api call URL
     * 
     * @return string Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
     */
    public function cacheKey($get_url)
    {
        return 'yesticket_' . \md5($get_url);
    }

    /**
     * Ensure the WP_TRANSIENTS $CACHE_KEY is in our active cache keys
     * 
     * @param string $CACHE_KEY Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
     */
    protected function addKeyToActiveCaches($CACHE_KEY)
    {
        $cacheKeys = \get_option('yesticket_transient_keys', array());
        if (!\in_array($CACHE_KEY, $cacheKeys)) {
            // unknown cache key, add to known keys
            $cacheKeys[] = $CACHE_KEY;
            \update_option('yesticket_transient_keys', $cacheKeys);
        }
    }

    /**
     * Clears the cached API request responses.
     * Resets the 'yesticket_transient_keys' option to an empty array.
     */
    public function clear()
    {
        \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Clearing Cache, triggered by user.'");
        $cacheKeys = \get_option('yesticket_transient_keys');
        \update_option('yesticket_transient_keys', array());
        foreach ($cacheKeys as $k) {
            \delete_transient($k);
        }
    }

    /**
     * Log out a request for new data, masking sensitive query args.
     * 
     * @param string $url the full api call URL
     */
    protected function logRequestMasked($url)
    {
        // https://www.php.net/manual/en/function.preg-replace.php
        $masked_url = \preg_replace('/organizer=\w+/', 'organizer=****', $url);
        $masked_url = \preg_replace('/key=\w+/', 'key=****', $masked_url);
        \ytp_log(__FILE__ . "@" . __LINE__ . ": 'No cache present, getting new data from: $masked_url'");
    }
}
