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
        ImageCache::$instance->ensureOptionExists();
        return ImageCache::$instance;
    }

    /**
     * Get JPEG image from the specified $url. 
     * 
     * @param string $url the full JPEG image URL
     * 
     * @return reource|GdImage image.
     */
    protected function getData($url)
    {
        $this->logRequestMasked($url);
        $image = @\imagecreatefromjpeg($url);
        if (!$image) {
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Could not retrieve image from $url'");
            throw new \RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", "yesticket"));
        }
        \ob_start();
        if (!@\imagejpeg($image, null, 100)) {
            \ob_end_clean();
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Could not output image from $url'");
            throw new \RuntimeException("Could not create image from $url");
        }
        return \ob_get_clean();
    }
}
