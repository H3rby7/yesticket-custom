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
        \ob_start();
        $image = \imagecreatefromjpeg($url);
        $msg = \ob_get_clean();
        if (!$image) {
            return $this->handleError($url, $msg);
        }
        \ob_start();
        if (!\imagejpeg($image, null, 100)) {
            $this->logImageMsg($url, \ob_get_clean());
            throw new \RuntimeException("Could not create JPEG from $url", 500);
        }
        return \ob_get_clean();
    }

    private function handleError($url, $msg)
    {
        if (\stripos($msg, 'Not a JPEG file:') === FALSE) {
            $this->logImageMsg($url, $msg);
            throw new \RuntimeException(__("The YesTicket service is currently unavailable. Please try again later.", "yesticket"));
        }
        return $this->getAsPNG($url);
    }

    /**
     * Get PNG image from $url
     * 
     * @param string $url of the remote image
     * @return string output image to be echoed
     */
    private function getAsPNG($url)
    {
        // START Capture message for the remote fopen operation
        \ob_start();
        $image = \imagecreatefrompng($url);
        $msg = \ob_get_clean();
        // END Capture message for the remote fopen operation
        if (!$image) {
            $image = $this->handleError($url, $msg);
        }
        // START Capture message for creating image locally
        \ob_start();
        if (!\imagepng($image, null, 100)) {
            $msg = \ob_get_clean();
            // END ERROR Capture message for creating image locally
            $this->logImageMsg($url, $msg);
            throw new \RuntimeException("Could not create PNG from $url", 500);
        }
        return \ob_get_clean();
        // END SUCCESS Capture message for creating image locally
    }

    /**
     * Log $msg, if present, or standard Error Message
     * 
     * @param string $url of the remote image
     * @param string $msg as captured from the \imagexxxx function
     */
    private function logImageMsg($url, $msg)
    {
        if (!empty($msg) && \strlen($msg) > 0) {
            \ytp_log(__FILE__ . "@" . __LINE__ . ": '$msg'");
        } else {
            \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Unknown Error when retrieving image from $url'");
        }
    }
}
