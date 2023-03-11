<?php

namespace YesTicket;

use \YesTicket\RestCache;
use \InvalidArgumentException;

include_once("rest_cache.php");
include_once("functions.php");
include_once("plugin_options.php");
include_once(__DIR__ . "/../model/event.php");

/**
 * Grants simplified access to the YesTicket API
 */
class Api
{
    /**
     * The $instance
     *
     * @var Api
     */
    static private $instance;

    /**
     * Get the $instance
     * 
     * @return Api $instance
     */
    static public function getInstance()
    {
        if (!isset(Api::$instance)) {
            Api::$instance = new Api();
        }
        return Api::$instance;
    }

    /**
     * The $instance
     *
     * @var RestCache
     */
    private $cache;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cache = RestCache::getInstance();
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
        return \max(\array_keys($this->apiEndpoints));
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
        if (empty(PluginOptions::getInstance()->getOrganizerID()) and empty($att["organizer"])) {
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
        if (empty(PluginOptions::getInstance()->getApiKey()) and empty($att["key"])) {
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
                \strcasecmp($type, "all") != 0 and
                \strcasecmp($type, "performance") != 0 and
                \strcasecmp($type, "workshop") != 0 and
                \strcasecmp($type, "festival") != 0
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
        if (isset($att["api-version"])) {
            if (!\array_key_exists($att["api-version"], $this->apiEndpoints)) {
                throw new InvalidArgumentException(
                    \sprintf(
                        /* translators: Error message, if the shortcode uses an invalid api-version - %1$s is replaced with the highest possible version */
                        __('The hidden field "api-version" must be an int bigger or equal to 1 and smaller or equal to %1$s.', "yesticket"),
                        $this->getLatestApiVersion()
                    )
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
        $result = null;
        if (empty($att["grep"])) {
            \ytp_log(__FILE__, __LINE__, "Getting events");
            // We don't  filter on our side. Easy API call.
            $result = $this->_getEvents($att);
        } else {
            // if we 'grep' (filter events manually on our side)
            $_count = empty($att["count"]) ? null : $att["count"];
            // we unset 'count' to call the api for more elements than needed.
            \ytp_log(__FILE__, __LINE__, "Getting events without 'count', because 'grep' is in use.");
            $att["count"] = null;
            $unfiltered = $this->_getEvents($att);
            $att["count"] = $_count;
            // we filter the items
            $result = $this->applyGrep($unfiltered, $att["grep"]);
        }
        if (empty($att["count"]) || !\is_numeric($att["count"]) || !\is_countable($result)) {
            // no count set or $result uncountable, just return list
            \ytp_log(__FILE__, __LINE__, "'Returning all events'");
            return $result;
        }
        \ytp_log(__FILE__, __LINE__, "Returning only " . $att["count"] . " events");
        // apply count to the list (because of 'grep' and to support counted events in case of v1 api call)
        return \array_slice($result, 0, $att["count"]);
    }

    /**
     * Build URL, get and transform into an array of @see \YesTicket\Model\Event
     * @param mixed $att of shortcode
     * @return array of @see \YesTicket\Model\Event
     */
    private function _getEvents($att)
    {
        $apiCall = $this->buildUrl($att, "events");
        $json = $this->cache->getFromCacheOrFresh($apiCall);
        return \array_map('\Yesticket\Model\Event::fromStdClass', \json_decode($json, false));
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
        $result = $this->cache->getFromCacheOrFresh($apiCall);
        $result = \json_decode($result);
        if (empty($att["count"]) || !\is_numeric($att["count"]) || !\is_countable($result)) {
            // no count set or $result uncountable, just return list
            \ytp_log(__FILE__, __LINE__, "Returning all testimonials");
            return $result;
        }
        \ytp_log(__FILE__, __LINE__, "Returning only " . $att["count"] . " testimonials");
        // apply count to the list (to support counted events in case of v1 api call)
        return \array_slice($result, 0, $att["count"]);
    }

    /**
     * Filter events by their event_name containing the string defined in $att["grep"]
     * 
     * @param mixed $eventList the list of events
     * @param array $grep string to find
     * @return mixed the filtered list
     */
    private function applyGrep($eventList, $grep = '')
    {
        return \array_filter($eventList, function ($item) use ($grep) {
            return \mb_stripos($item->event_name, $grep) !== FALSE;
        });
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

    private function addToArrayIfValueNotEmpty(&$arr, $key, $value)
    {
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
            return \strtolower($att[$key]);
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
        $lang = get_locale();
        if (function_exists('\locale_get_primary_language')) {
            return \locale_get_primary_language($lang);
        } else {
            $langUnderscorePos = strpos($lang, "_");
            if ($langUnderscorePos != false and $langUnderscorePos > -1) {
                $lang = substr($lang, 0, $langUnderscorePos);
            }
            return $lang;
        }
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
            return PluginOptions::getInstance()->getOrganizerID();
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
            return PluginOptions::getInstance()->getApiKey();
        }
    }
}
