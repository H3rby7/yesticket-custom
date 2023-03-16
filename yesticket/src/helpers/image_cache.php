<?php

namespace YesTicket;

use WP_Error;
use \YesTicket\Cache;
use YesTicket\Model\CachedImage;

include_once("cache.php");
include_once("functions.php");
include_once("plugin_options.php");
include_once(__DIR__ . "/../model/cached_image.php");
/**
 * Cache for YesTicket API Calls
 */
class ImageCache extends Cache
{
    /**
     * @return ImageCache $instance
     */
    static public function getInstance()
    {
        if (!isset(ImageCache::$instance)) {
            ImageCache::$instance = new ImageCache();
        }
        return ImageCache::$instance;
    }

    
    /**
     * Take cached response for $get_url, or get new using the $getFunction.
     * 
     * @param string $get_url the full api call URL
     * @param callable $getFunction used to fetch image, if not already in cache
     * 
     * @return CachedImage|WP_Error image
     * 
     */
    public function getFromCacheOrFresh($get_url, $getFunction)
    {
        $CACHE_KEY = $this->cacheKey($get_url);
        $image = null;
        // check if we have cached information
        $transient = get_transient($CACHE_KEY);
        if (false === $transient) {
            // Cache not present, we make the API call
            $image = $getFunction($get_url);
            if ($image instanceof CachedImage) {
                // Need to manually serialize, as the automatic one fails in the unserialize step
                $this->cache($CACHE_KEY, $image->serialize());
            }
        } else {
            $image = new CachedImage();
            $image->unserialize($transient);
            $content_type = $image->get_content_type();
            \ytp_debug(__FILE__, __LINE__, "Taken from Cache content_type => '$content_type'; original url => '$get_url'");
            // Need to manually unserialize, as the automatic would fail in this step
        }
        // at this time we have our image, either from cache or after an API call.
        return $image;
    }

}
