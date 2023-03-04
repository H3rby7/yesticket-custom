<?php

namespace YesTicket;

use \YesTicket\Cache;
use \YesTicket\ImageException;
use \YesTicket\ImageNotFoundException;
use YesTicket\Model\CachedImage;
use \YesTicket\WrongImageTypeException;

include_once("cache.php");
include_once("functions.php");
include_once("plugin_options.php");
include_once(__DIR__ . "/../exceptions/image_exceptions.php");
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
        ImageCache::$instance->ensureOptionExists();
        return ImageCache::$instance;
    }

    
    /**
     * Get image from the specified $get_url using $fetchFunction and render using $renderFunction 
     * Use cached response, if present, else we make a new call and sve the image to cache
     * 
     * @param string $get_url the full api call URL
     * @param string expected $type of the image
     * @param callable $fetchFunction used to fetch image
     * @param callable $renderFunction used to render image
     * 
     * @return CachedImage image
     * 
     * @throws ImageNotFoundException if the $get_url returns html and not an image (usually meaning ERROR)
     * @throws WrongImageTypeException if the image behind $get_url is of a different type 
     * @throws ImageException if unknown error occurs
     */
    public function getFromCacheOrFresh($get_url, $type, $fetchFunction, $renderFunction)
    {
        $CACHE_KEY = $this->cacheKey($get_url);
        $image = null;
        // check if we have cached information
        $transient = get_transient($CACHE_KEY);
        if (false === $transient) {
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Try getting as Content-Type $type; url => $get_url'");
            // Cache not present, we make the API call
            $data = $this->getData($get_url, $fetchFunction, $renderFunction);
            $image = new CachedImage($type, $data);
            // Need to manually serialize, as the automatic one fails in the unserialize step
            $this->cache($CACHE_KEY, $image->serialize());
        } else {
            $image = new CachedImage();
            $image->unserialize($transient);
            $content_type = $image->get_content_type();
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Taken from Cache ($content_type); original url => $get_url'");
            // Need to manually unserialize, as the automatic would fail in this step
        }
        // at this time we have our image, either from cache or after an API call.
        return $image;
    }

    /**
     * Get image from the specified $get_url using $fetchFunction and render using $renderFunction 
     * 
     * @param string $get_url the full image URL
     * @param callable $fetchFunction used to fetch image using $get_url
     * @param callable $renderFunction used to render image with the result of $fetchFunction
     * 
     * @return string echoable image content
     * 
     * @throws ImageNotFoundException if the $get_url returns html and not an image (usually meaning ERROR)
     * @throws WrongImageTypeException if the image behind $get_url is of a different type 
     * @throws ImageException if unknown error occurs
     */
    protected function getData($get_url, $fetchFunction, $renderFunction)
    {
        \ob_start();
        $image = $fetchFunction($get_url);
        $msg = \ob_get_clean();
        if (!$image) {
            throw $this->generateError($msg);
        }
        \ob_start();
        if (!$renderFunction($image)) {
            $msg = \ob_get_clean();
            throw $this->generateError($msg);
        }
        return \ob_get_clean();
    }

    /**
     * Create an exception, depending on $msg
     * 
     * @param string $msg output of \image????? function
     * 
     * @return ImageException
     */
    private function generateError($msg)
    {
        // response starts with '<h' (probably <html)
        if (\stripos($msg, 'starts with 0x3c 0x68') !== FALSE) {
            return new ImageNotFoundException($msg, 404);
        }
        // response starts with meta symbol '<control>' (probably different image type than we expected)
        if (\stripos($msg, 'starts with 0x89') !== FALSE) {
            return new WrongImageTypeException($msg);
        }
        return new ImageException($msg);
    }
}
