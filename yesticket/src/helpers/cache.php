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
     * Cache the result
     * 
     * @param string $CACHE_KEY Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
     * @param mixed $data the data
     */
    protected function cache($CACHE_KEY, $data)
    {
        $saved = \set_transient($CACHE_KEY, $data, PluginOptions::getInstance()->getCacheTimeInMinutes() * MINUTE_IN_SECONDS);
        if (!$saved) {
            // @codeCoverageIgnoreStart
            \ytp_log(__FILE__, __LINE__, "Could not cache item '$CACHE_KEY'");
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Clears all transient/cache items
     * @param wpdb $wpdb DB connection
     * @return boolean TRUE if all cached items could be deleted. FALSE if any errors occured.
     */
    static public function clear($wpdb)
    {
        // https://developer.wordpress.org/reference/classes/wpdb/get_results/
        $queryResult = $wpdb->get_results("SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_yesticket%'", ARRAY_N);
        if ($wpdb->last_error || empty($queryResult) || !\is_array($queryResult)) {
            \ytp_log(__FILE__, __LINE__, "DB Query failed, cannot clear cache. " . $wpdb->last_error);
            return FALSE;
        }
        $cacheKeys = \array_map(function ($row) {
            return \str_replace('_transient_', '', $row[0]);
        }, $queryResult);
        $count = \count($cacheKeys);
        $success = TRUE;
        \ytp_log(__FILE__, __LINE__, "Clearing $count cache items, triggered by user.");
        foreach ($cacheKeys as $k) {
            if (!\delete_transient($k)) {
                $success = FALSE;
            }
        }
        return $success;
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
        \ytp_log(__FILE__, __LINE__, "No cache present, getting new data from: '$masked_url'");
    }
}
