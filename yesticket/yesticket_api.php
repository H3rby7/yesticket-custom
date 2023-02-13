<?php

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

    private function getLatestVersion()
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
            $ctx = stream_context_create(array(
                'http' =>
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

    private function validateArguments($att, $options)
    {
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
        if (!empty($options["api-version"])) {
            $apiVersion = $options["api-version"];
            if (!is_numeric($apiVersion)) {
                throw new InvalidArgumentException(
                    /* translators: Error message, if the shortcode uses a non-numeric api-version */
                    __("The hidden field 'api-version' must be numeric.", "yesticket")
                );
            }
            $latestApiVersion = $this->getLatestVersion();
            if ($apiVersion > $latestApiVersion) {
                throw new InvalidArgumentException(
                    /* translators: Error message, if the shortcode uses an unknown api-version */
                    __("The hidden field 'api-version' must provide a valid version.", "yesticket")
                );
            }
        }
        return true;
    }

    public function getEvents($att)
    {
        return $this->getDataCached($this->validateAndBuildUrl($att, "events"));
    }

    public function getTestimonials($att)
    {
        return $this->getDataCached($this->validateAndBuildUrl($att, "testimonials"));
    }

    private function validateAndBuildUrl($att, $type)
    {
        $env_add = "";
        if ($att["env"] == 'dev') {
            $env_add = "/dev";
        }
        $options = get_option('yesticket_settings_required');
        $this->validateArguments($att, $options);
        // Define API Version
        $apiVersion = $this->getLatestVersion();
        if (!empty($att["api-version"])) {
            $apiVersion = $att["api-version"];
        }
        $apiEndpoint = $this->apiEndpoints[$apiVersion][$type];
        // Build endpoint url
        $get_url = 'https://www.yesticket.org' . $env_add . '/api/' . $apiEndpoint;
        ytp_log(__FILE__ . "@" . __LINE__ . ": 'Calling API Endpoint: $get_url'");
        // Add query parameters
        $get_url .= $this->buildQueryParams($att, $options);
        return $get_url;
    }

    private function buildQueryParams($att, $options)
    {
        $queryParams = '';
        if (!empty($att["count"])) {
            $queryParams .= '&count=' . strtolower($att["count"]);
        }
        if (!empty($att["type"])) {
            $queryParams .= '&type=' . strtolower($att["type"]);
        }
        $lang = get_locale();
        $langUnderscorePos = strpos($lang, "_");
        if ($langUnderscorePos != false and $langUnderscorePos > -1) {
            $lang = substr($lang, 0, $langUnderscorePos);
        }
        $queryParams .= '&lang=' . $lang;
        ytp_log(__FILE__ . "@" . __LINE__ . ": 'Public query params for API Call: " . $queryParams . "'");
        // We keep organizedID and key out of the ytp_log.
        $secretQueryParams = '';
        if (!empty($att["organizer"])) {
            $secretQueryParams .= '?organizer=' . $att["organizer"];
        } else {
            $secretQueryParams .= '?organizer=' . $options["organizer_id"];
        }
        if (!empty($att["key"])) {
            $secretQueryParams .= '&key=' . $att["key"];
        } else {
            $secretQueryParams .= '&key=' . $options["api_key"];
        }
        return $secretQueryParams . $queryParams;
    }
} ?>