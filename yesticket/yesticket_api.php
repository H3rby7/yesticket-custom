<?php

// In this array we store the keys for the wp cache, so we can clear our cache if demanded.
add_option('yesticket_transient_keys', array());

class YesTicketApi
{

    static private $instance;

    static public function getInstance()
    {
        if (!isset(YesTicketApi::$instance)) {
            YesTicketApi::$instance = new YesTicketApi();
        }
        return YesTicketApi::$instance;
    }

    private $apiEndpoints = array(
        '1' => array(
            'events' => 'events-endpoint.php',
            'testimonials' => 'testimonials-endpoint.php',
        ),
        '2' => array(
            'events' => 'v2/events.php',
            'testimonials' => 'v2/testimonials.php',
        ),
    );

    private function getLatestApiVersion()
    {
        return count($this->apiEndpoints);
    }

    private function getDataCached($get_url)
    {
        $CACHE_TIME_IN_MINUTES = YesTicketPluginOptions::getInstance()->getCacheTimeInMinutes();
        $CACHE_KEY = ytp_cacheKey($get_url);

        // check if we have cached information
        $data = get_transient($CACHE_KEY);
        if (false === $data) {
            // Cache not present, we make the API call
            $data = $this->getData($get_url);
            set_transient($CACHE_KEY, $data, $CACHE_TIME_IN_MINUTES * MINUTE_IN_SECONDS);
            // save cache key to options, so we can delete the transient, if necessary
            ytp_addCacheKeyToOptions($CACHE_KEY);
        }
        // at this time we have our data, either from cache or after an API call.
        return $data;
    }

    private function getData($get_url)
    {
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

    private function validateArguments($att)
    {
        $this->throw_on_missing_organizer_id($att);
        $this->throw_on_missing_api_key($att);
        $this->throw_on_invalid_att_type($att);
        $this->throw_on_invalid_api_version($att);
    }

    private function throw_on_missing_organizer_id($att) {
        if (empty(YesTicketPluginOptions::getInstance()->getOrganizerID()) and empty($att["organizer"])) {
            throw new InvalidArgumentException(
                /* translators: Error message, if the plugin is not properly configured*/
                __("Please configure your 'organizer-id' in the plugin settings.", "yesticket")
            );
        }
    }

    private function throw_on_missing_api_key($att) {
        if (empty(YesTicketPluginOptions::getInstance()->getApiKey()) and empty($att["key"])) {
            throw new InvalidArgumentException(
                /* translators: Error message, if the plugin is not properly configured*/
                __("Please configure your 'key' in the plugin settings.", "yesticket")
            );
        }
    }

    private function throw_on_invalid_att_type($att) {
        if (!empty($att["type"])) {
            $type = $att["type"];
            if (
                !strcasecmp($type, "all") and
                !strcasecmp($type, "performance") and
                !strcasecmp($type, "workshop") and
                !strcasecmp($type, "festival")
            ) {
                throw new InvalidArgumentException(
                    /* translators: Error message, if the shortcode uses wrong/invalid types*/
                    __("Please provide a valid 'type'. If you omit the attribute it will default to 'all'. Possible options are 'all', 'performance', 'workshop' and 'festival'.", "yesticket")
                );
            }
        }
    }

    private function throw_on_invalid_api_version($att) {
        if (!empty($att["api-version"])) {
            $apiVersion = $att["api-version"];
            if (!is_numeric($apiVersion)) {
                throw new InvalidArgumentException(
                    /* translators: Error message, if the shortcode uses a non-numeric api-version */
                    __("The hidden field 'api-version' must be numeric.", "yesticket")
                );
            }
            $latestApiVersion = $this->getLatestApiVersion();
            if ($apiVersion > $latestApiVersion) {
                throw new InvalidArgumentException(
                    /* translators: Error message, if the shortcode uses an unknown api-version */
                    __("The hidden field 'api-version' must provide a valid version.", "yesticket")
                );
            }
        }
    }

    public function getEvents($att)
    {
        $this->validateArguments($att);
        $apiCall = $this->buildUrl($att, "events");
        return $this->getDataCached($apiCall);
    }

    public function getTestimonials($att)
    {
        $this->validateArguments($att);
        $apiCall = $this->buildUrl($att, "testimonials");
        return $this->getDataCached($apiCall);
    }

    private function buildUrl($att, $type)
    {
        // Check to add 'env' path to API call
        $env_add = "";
        if ($att["env"] == 'dev') {
            $env_add = "/dev";
        }

        $api_endpoint = $this->getApiEndpoint($att, $type);

        // Build endpoint url
        $get_url = "https://www.yesticket.org$env_add/api/$api_endpoint";
        ytp_log(__FILE__ . "@" . __LINE__ . ": 'Calling API Endpoint: $get_url'");

        // Add query parameters
        $get_url .= $this->buildQueryParams($att);
        return $get_url;
    }

    private function getApiEndpoint($att, $type) {
        $apiVersion = $this->getLatestApiVersion();
        if (!empty($att["api-version"])) {
            $apiVersion = $att["api-version"];
        }
        return $this->apiEndpoints[$apiVersion][$type];
    }

    private function buildQueryParams($att)
    {
        $queryParams = '';
        $queryParams .= $this->getAttLC($att, "count");
        $queryParams .= $this->getAttLC($att, "type");
        $queryParams .= $this->getLocaleQuery();
        ytp_log(__FILE__ . "@" . __LINE__ . ": 'Public query params for API Call: " . $queryParams . "'");
        // We keep organizedID and key out of the ytp_log.
        $secretQueryParams = '';
        $secretQueryParams .= $this->getOrganizerQuery($att);
        $secretQueryParams .= $this->getApiKeyQuery($att);
        return $secretQueryParams . $queryParams;
    }

    private function getAttLC($att, $key)
    {
        if (!empty($att[$key])) {
            return "&$key=" . strtolower($att[$key]);
        }
        return '';
    }

    private function getLocaleQuery()
    {
        $lang = get_locale();
        $langUnderscorePos = strpos($lang, "_");
        if ($langUnderscorePos != false and $langUnderscorePos > -1) {
            $lang = substr($lang, 0, $langUnderscorePos);
        }
        return "&lang=$lang";
    }

    private function getOrganizerQuery($att)
    {
        if (!empty($att["organizer"])) {
            return '?organizer=' . $att["organizer"];
        } else {
            return '?organizer=' . YesTicketPluginOptions::getInstance()->getOrganizerID();
        }
    }

    private function getApiKeyQuery($att)
    {
        if (!empty($att["key"])) {
            return '&key=' . $att["key"];
        } else {
            return '&key=' . YesTicketPluginOptions::getInstance()->getApiKey();
        }
    }
}
