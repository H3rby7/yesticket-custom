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
            \update_option('yesticket_transient_keys', array(), false);
        }
        add_action('ytp_add_cache_key', [$this, 'addKeyToActiveCaches'], 10, 2);
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
     * @param int (optional) $attemptCount increased during scheduled events
     * @return boolean true if cached or caching successfully scheduled.
     */
    protected function addKeyToActiveCaches($CACHE_KEY, $attemptCount = 0)
    {
        if ($attemptCount > 5) {
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Failed adding $CACHE_KEY to yesticket_transient_keys; removing from cache.'");
            \delete_transient($CACHE_KEY);
            return;
        }
        if (\get_transient('yesticket_transient_keys_lock')) {
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Lock is in use.'");
            return \wp_schedule_single_event(\time(), 'ytp_add_cache_key', [$CACHE_KEY, ++$attemptCount]);
        }
        \set_transient('yesticket_transient_keys_lock', $CACHE_KEY, 1);
        if (\get_transient('yesticket_transient_keys_lock') !== $CACHE_KEY) {
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Lost race condition. Lock is in use.'");
            return \wp_schedule_single_event(\time(), 'ytp_add_cache_key', [$CACHE_KEY, ++$attemptCount]);
        }
        // At this time we are 90% save (due to this make-shift locking)
        $cacheKeys = \get_option('yesticket_transient_keys', array());
        $success = false;
        if (!\in_array($CACHE_KEY, $cacheKeys)) {
            // unknown cache key, add to known keys
            $cacheKeys[] = $CACHE_KEY;
            $success = \update_option('yesticket_transient_keys', $cacheKeys, false);
        }
        \delete_transient('yesticket_transient_keys_lock');
        return $success;
    }

    /**
     * Cache the result
     * 
     * @param string $CACHE_KEY Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
     * @param mixed $data the data
     */
    protected function cache($CACHE_KEY, $data)
    {
        $saved = \set_transient($CACHE_KEY, $data, PluginOptions::getInstance()->getCacheTimeInMinutes() * MINUTE_IN_SECONDS);
        if ($saved) {
            // save cache key to options, so we can delete the transient, if necessary
            $saved = $this->addKeyToActiveCaches($CACHE_KEY);
        }
        if (!$saved) {
            // @codeCoverageIgnoreStart
            \delete_transient($CACHE_KEY);
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Could not cache item $CACHE_KEY'");
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Clears the cached API request responses.
     * Resets the 'yesticket_transient_keys' option to an empty array.
     */
    static public function clear()
    {
        $cacheKeys = \get_option('yesticket_transient_keys', array());
        $count = \count($cacheKeys);
        \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Clearing $count cache items, triggered by user.'");
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
