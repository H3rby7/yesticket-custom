<?php

// In this array we store the keys for the wp cache, so we can clear our cache if demanded.
add_option('yesticket_transient_keys', array());

class YesTicketCache
{

    static private $instance;

    static public function getInstance()
    {
        if (!isset(YesTicketCache::$instance)) {
            YesTicketCache::$instance = new YesTicketCache();
        }
        return YesTicketCache::$instance;
    }

    public function getFromCacheOrFresh($get_url)
    {
        $CACHE_TIME_IN_MINUTES = YesTicketPluginOptions::getInstance()->getCacheTimeInMinutes();
        $CACHE_KEY = $this->cacheKey($get_url);

        // check if we have cached information
        $data = get_transient($CACHE_KEY);
        if (false === $data) {
            // Cache not present, we make the API call
            $data = $this->getData($get_url);
            set_transient($CACHE_KEY, $data, $CACHE_TIME_IN_MINUTES * MINUTE_IN_SECONDS);
            // save cache key to options, so we can delete the transient, if necessary
            $this->addKeyToActiveCaches($CACHE_KEY);
        }
        // at this time we have our data, either from cache or after an API call.
        return $data;
    }

    private function getData($get_url)
    {
        $this->logRequestMasked($get_url);
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
            $ctx = stream_context_create(array(
                'http' =>
                array(
                    'timeout' => 4,  // seconds
                )
            ));
            $get_content = file_get_contents($get_url, 0, $ctx);
        } else {
            throw new Exception('We require "cURL" or "allow_url_fopen". Please contact your web hosting provider to install/activate one of the features.');
        }
        if (empty($get_content) && file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
            // in Case of a CURL-error
            ini_set('default_socket_timeout', 4);
            $ctx = stream_context_create(array(
                'http' =>
                array(
                    'timeout' => 4,  // seconds
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

    private function cacheKey($get_url)
    {
        // common key specific to yesticket to set and retrieve WP_TRANSIENTS
        return 'yesticket_' . md5($get_url);
    }

    private function addKeyToActiveCaches($CACHE_KEY)
    {
        $cacheKeys = get_option('yesticket_transient_keys', array());
        if (!in_array($CACHE_KEY, $cacheKeys)) {
            // unknown cache key, add to known keys
            $cacheKeys[] = $CACHE_KEY;
            update_option('yesticket_transient_keys', $cacheKeys);
        }
    }

    /**
     * Clears the cached API request responses.
     */
    public function clear()
    {
        ytp_log(__FILE__ . "@" . __LINE__ . ": 'Clearing Cache, triggered by user.'");
        $cacheKeys = get_option('yesticket_transient_keys');
        update_option('yesticket_transient_keys', array());
        foreach ($cacheKeys as $k) {
            delete_transient($k);
        }
    }

    private function logRequestMasked($url) {
        // https://www.php.net/manual/en/function.preg-replace.php
        $masked_url = preg_replace('/organizer=\w+/', 'organizer=****', $url);
        $masked_url = preg_replace('/key=\w+/', 'key=****', $masked_url);
        ytp_log(__FILE__ . "@" . __LINE__ . ": 'No cache present, getting new data from: $masked_url'");
    }
}
