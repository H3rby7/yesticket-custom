<?php

namespace YesTicket;

include_once("cache.php");
include_once("functions.php");
include_once("plugin_options.php");
/**
 * Cache for YesTicket API Calls
 */
class ImageCache extends Cache
{
    /**
     * Get the $instance
     * 
     * @return ImageCache $instance
     */
    static public function getInstance()
    {
        if (!isset(ImageCache::$instance)) {
            ImageCache::$instance = new ImageCache();
        }
        if (!\get_option('yesticket_transient_keys', false)) {
            \add_option('yesticket_transient_keys', array());
        }
        return ImageCache::$instance;
    }

    /**
     * Get image from the specified $get_url. 
     * Use cached response, if present, else we make a new call and sve the data to cache
     * 
     * @param string $get_url the full Image URL
     * 
     * @return reource|GdImage image.
     */
    public function getFromCacheOrFresh($get_url)
    {
        $CACHE_TIME_IN_MINUTES = PluginOptions::getInstance()->getCacheTimeInMinutes();
        $CACHE_KEY = $this->cacheKey($get_url);

        $filePath = $this->getCacheFilePath($CACHE_KEY);
        // check if the transient for this $get_url is present AND the image is present at $filepath
        if (false != get_transient($CACHE_KEY) && \file_exists($filePath)) {
            $image = @\imagecreatefromjpeg($filePath);
            if ($image) {
                return $image;
            } else {
                \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Could not load cached image from $filePath'");
            }
        }
        // Cache not present, we make the API call
        $image = $this->getData($get_url);
        // Save the image to FS
        \imagejpeg($image, $filePath, 100);
        // Save reference to the image in our cache.
        \set_transient($CACHE_KEY, $filePath, $CACHE_TIME_IN_MINUTES * MINUTE_IN_SECONDS);
        // save cache key to options, so we can delete the transient, if necessary
        $this->addKeyToActiveCaches($CACHE_KEY);
        return $image;
    }

    private function getCacheFilePath($CACHE_KEY) {
        return \get_temp_dir() . "/$CACHE_KEY.jpeg";
    }

    /**
     * Get JPEG image from the specified $get_url. 
     * 
     * @param string $get_url the full JPEG image URL
     * 
     * @return reource|GdImage image.
     */
    protected function getData($get_url)
    {
        $image = @\imagecreatefromjpeg($get_url);
        if (!$image) {
            throw new \RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", "yesticket"));
        }
        return $image;
    }

    public function clear()
    {
        \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Clearing cached image files, triggered by user.'");
        $cachedImageFiles = \array_filter(\scandir(\get_temp_dir()), function ($item) {
            return \mb_stripos($item, 'yesticket_') !== FALSE;
        });
        foreach ($cachedImageFiles as $file) {
            $filePath = \get_temp_dir() . $file;
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Removing image $filePath'");
            \unlink($filePath);
        }
    }
}
