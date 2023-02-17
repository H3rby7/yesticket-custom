<?php

include_once "yesticket_cache.php";

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

    private $cache;
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cache = YesTicketCache::getInstance();
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

    private function validateArguments($att)
    {
        $this->throw_on_missing_organizer_id($att);
        $this->throw_on_missing_api_key($att);
        $this->throw_on_invalid_att_type($att);
        $this->throw_on_invalid_api_version($att);
    }

    private function throw_on_missing_organizer_id($att)
    {
        if (empty(YesTicketPluginOptions::getInstance()->getOrganizerID()) and empty($att["organizer"])) {
            throw new InvalidArgumentException(
                /* translators: Error message, if the plugin is not properly configured*/
                __("Please configure your 'organizer-id' in the plugin settings.", "yesticket")
            );
        }
    }

    private function throw_on_missing_api_key($att)
    {
        if (empty(YesTicketPluginOptions::getInstance()->getApiKey()) and empty($att["key"])) {
            throw new InvalidArgumentException(
                /* translators: Error message, if the plugin is not properly configured*/
                __("Please configure your 'key' in the plugin settings.", "yesticket")
            );
        }
    }

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

    public function getEvents($att)
    {
        $this->validateArguments($att);
        $apiCall = $this->buildUrl($att, "events");
        return $this->cache->getFromCacheOrFresh($apiCall);
    }

    public function getTestimonials($att)
    {
        $this->validateArguments($att);
        $apiCall = $this->buildUrl($att, "testimonials");
        return $this->cache->getFromCacheOrFresh($apiCall);
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

        // Add query parameters
        $get_url .= $this->buildQueryParams($att);
        return $get_url;
    }

    private function getApiEndpoint($att, $type)
    {
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
        $queryParams .= $this->getOrganizerQuery($att);
        $queryParams .= $this->getApiKeyQuery($att);
        return $queryParams;
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
