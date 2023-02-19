<?php

include_once "yesticket_cache.php";

/**
 * Grants simplified access to the YesTicket API
 */
class YesTicketApi
{
    /**
     * The $instance
     *
     * @var YesTicketApi
     */
    static private $instance;

    /**
     * Get the $instance
     * 
     * @return YesTicketApi $instance
     */
    static public function getInstance()
    {
        if (!isset(YesTicketApi::$instance)) {
            YesTicketApi::$instance = new YesTicketApi();
        }
        return YesTicketApi::$instance;
    }

    /**
     * The $instance
     *
     * @var YesTicketCache
     */
    private $cache;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cache = YesTicketCache::getInstance();
    }

    /**
     * $apiEndpoints versioned
     *
     * @var array(array)
     */
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

    /**
     * Get the latest API version
     * 
     * @return number the latest version, derived from $apiEndpoints
     */
    private function getLatestApiVersion()
    {
        return count($this->apiEndpoints);
    }

    /**
     * Validates the given $att and saved options to make sure we can build a valid API-call.
     * 
     * @param mixed $att of shortcode
     * 
     * @throws InvalidArgumentException if anything is missing or wrong
     */
    private function validateArguments($att)
    {
        $this->throw_on_missing_organizer_id($att);
        $this->throw_on_missing_api_key($att);
        $this->throw_on_invalid_att_type($att);
        $this->throw_on_invalid_api_version($att);
    }

    /**
     * Validate we have an organizer from settings or shortcode.
     * 
     * @param mixed $att of shortcode
     * 
     * @throws InvalidArgumentException if no organizer-id is configured
     */
    private function throw_on_missing_organizer_id($att)
    {
        if (empty(YesTicketPluginOptions::getInstance()->getOrganizerID()) and empty($att["organizer"])) {
            throw new InvalidArgumentException(
                /* translators: Error message, if the plugin is not properly configured*/
                __("Please configure your 'organizer-id' in the plugin settings.", "yesticket")
            );
        }
    }

    /**
     * Validate we have an api key from settings or shortcode.
     * 
     * @param mixed $att of shortcode
     * 
     * @throws InvalidArgumentException if no key is configured
     */
    private function throw_on_missing_api_key($att)
    {
        if (empty(YesTicketPluginOptions::getInstance()->getApiKey()) and empty($att["key"])) {
            throw new InvalidArgumentException(
                /* translators: Error message, if the plugin is not properly configured*/
                __("Please configure your 'key' in the plugin settings.", "yesticket")
            );
        }
    }

    /**
     * Validate the specified 'type' is valid.
     * 
     * @param mixed $att of shortcode
     * 
     * @throws InvalidArgumentException if type is not valid.
     */
    private function throw_on_invalid_att_type($att)
    {
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

    /**
     * Validate the api version is valid. (numeric and smaller than the latest version)
     * 
     * @param mixed $att of shortcode
     * 
     * @throws InvalidArgumentException if the api-version is invalid
     */
    private function throw_on_invalid_api_version($att)
    {
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

    /**
     * Validate API call and get events from API
     * 
     * @param mixed $att of shortcode
     * 
     * @return mixed the events
     */
    public function getEvents($att)
    {
        $this->validateArguments($att);
        $apiCall = $this->buildUrl($att, "events");
        return $this->cache->getFromCacheOrFresh($apiCall);
    }

    /**
     * Validate API call and get testimonials from API
     * 
     * @param mixed $att of shortcode
     * 
     * @return mixed the testimonials
     */
    public function getTestimonials($att)
    {
        $this->validateArguments($att);
        $apiCall = $this->buildUrl($att, "testimonials");
        return $this->cache->getFromCacheOrFresh($apiCall);
    }

    /**
     * Build URL for API call
     * 
     * @param mixed $att of shortcode
     * @param string $type {events|testimonials}
     * 
     * @return string the API URL
     */
    private function buildUrl($att, $type)
    {
        // Check to add 'env' path to API call
        $env_add = "";
        if (!empty($att["env"]) && $att["env"] == 'dev') {
            $env_add = "/dev";
        }

        $api_endpoint = $this->getApiEndpoint($att, $type);

        // Build endpoint url
        $get_url = "https://www.yesticket.org$env_add/api/$api_endpoint";

        // Add query parameters
        $get_url .= $this->buildQueryParams($att);
        return $get_url;
    }

    /**
     * Build endpoint URL (no query params)
     * 
     * @param mixed $att of shortcode
     * @param string $type {events|testimonials}
     * 
     * @return string the endpoint URL
     */
    private function getApiEndpoint($att, $type)
    {
        $apiVersion = $this->getLatestApiVersion();
        if (!empty($att["api-version"])) {
            $apiVersion = $att["api-version"];
        }
        return $this->apiEndpoints[$apiVersion][$type];
    }

    /**
     * Build query string
     * 
     * @param mixed $att of shortcode
     * 
     * @return string the query string
     */
    private function buildQueryParams($att)
    {
        $params = array();
        $this->addToArrayIfValueNotEmpty($params, "count", $this->getAttLC($att, "count"));
        $this->addToArrayIfValueNotEmpty($params, "type", $this->getAttLC($att, "type"));
        $this->addToArrayIfValueNotEmpty($params, "lang", $this->getLocale());
        $this->addToArrayIfValueNotEmpty($params, "organizer", $this->getOrganizer($att));
        $this->addToArrayIfValueNotEmpty($params, "key", $this->getApiKey($att));
        return "?" . http_build_query($params);
    }

    private function addToArrayIfValueNotEmpty(&$arr, $key, $value) {
        if (!empty($value)) {
            $arr[$key] = $value;
        }
    }

    /**
     * Get $key of shortcode $att
     * 
     * @param mixed $att of shortcode
     * @param string $key which key of the $att
     * 
     * @return string the value in lowercase
     */
    private function getAttLC($att, $key)
    {
        if (!empty($att[$key])) {
            return strtolower($att[$key]);
        }
        return '';
    }

    /**
     * Get the primary language of the current locale
     * 
     * @return string primary language
     */
    private function getLocale()
    {
        return locale_get_primary_language(get_locale());
    }

    /**
     * Get organizer
     * Usually takes option value as set for plugin
     * Prefers deprecated value from $att
     * 
     * @param mixed $att of shortcode
     * 
     * @return string organizer
     */
    private function getOrganizer($att)
    {
        if (!empty($att["organizer"])) {
            return $att["organizer"];
        } else {
            return YesTicketPluginOptions::getInstance()->getOrganizerID();
        }
    }

    /**
     * Get api key
     * Usually takes option value as set for plugin
     * Prefers deprecated value from $att
     * 
     * @param mixed $att of shortcode
     * 
     * @return string api key
     */
    private function getApiKey($att)
    {
        if (!empty($att["key"])) {
            return $att["key"];
        } else {
            return YesTicketPluginOptions::getInstance()->getApiKey();
        }
    }
}
