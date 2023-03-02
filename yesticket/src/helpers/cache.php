<?php

namespace YesTicket;

include_once("functions.php");
include_once("plugin_options.php");
/**
 * Cache for YesTicket API Calls
 */
abstract class Cache
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
    abstract static public function getInstance();

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
     * Cache the result
     * 
     * @param string $CACHE_KEY Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
     * @param mixed $data the data
     */
    protected function cache($CACHE_KEY, $data)
    {
        \set_transient($CACHE_KEY, $data, PluginOptions::getInstance()->getCacheTimeInMinutes() * MINUTE_IN_SECONDS);
        // save cache key to options, so we can delete the transient, if necessary
        $this->addKeyToActiveCaches($CACHE_KEY);
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
