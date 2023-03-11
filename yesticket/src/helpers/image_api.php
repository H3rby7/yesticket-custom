<?php

namespace YesTicket;

use \YesTicket\ImageCache;
use \YesTicket\Model\CachedImage;

include_once("functions.php");
include_once("image_cache.php");
include_once("plugin_options.php");
include_once(__DIR__ . "/../model/cached_image.php");

/**
 * Grants simplified access to the YesTicket API
 */
class ImageApi
{
    /**
     * The $instance
     *
     * @var ImageApi
     */
    static private $instance;

    /**
     * Get the $instance
     * 
     * @return ImageApi $instance
     */
    static public function getInstance()
    {
        if (!isset(ImageApi::$instance)) {
            ImageApi::$instance = new ImageApi();
        }
        return ImageApi::$instance;
    }

    /**
     * The $instance
     *
     * @var ImageCache
     */
    private $cache;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cache = ImageCache::getInstance();
    }

    /**
     * Get Event image from yesticket for given $event_id
     * 
     * @param int $event_id
     * 
     * @return CachedImage image
     * 
     * @throws ImageException if image cannot be loaded.
     */
    public function getEventImage($event_id)
    {
        $yesTicketImageUrl = $this->getYesTicketUrlOfImage($event_id);
        try {
            return $this->_getEventImage($yesTicketImageUrl);
        } catch (ImageException $e) {
            $msg = $e->getMessage();
            if (!empty($msg) && \strlen($msg) > 0) {
                \ytp_info(__FILE__, __LINE__, "Unknown Error: $msg");
            } else {
                \ytp_info(__FILE__, __LINE__, "Unknown Error when retrieving image from '$yesTicketImageUrl'");
            }
            throw $e;
        }
    }

    public function getYesTicketUrlOfImage($event_id) {
        return "https://www.yesticket.org/dev/picture.php?event=" . $event_id;
    }

    /**
     * Get image from $get_url
     * Attempt to load as:
     * 1. JPEG
     * 2. PNG
     * then gives up
     * 
     * @param string $get_url of the image
     * 
     * @return CachedImage image
     * 
     * @throws ImageException if image cannot be loaded.
     */
    private function _getEventImage($get_url)
    {
        try {
            return $this->cache->getFromCacheOrFresh($get_url, 'image/jpeg', '\imagecreatefromjpeg', function ($image) {
                return \imagejpeg($image, null, 100);
            });
        } catch (WrongImageTypeException $e) {
            return $this->cache->getFromCacheOrFresh($get_url, 'image/png', '\imagecreatefrompng', function ($image) {
                return \imagepng($image, null, 0);
            });
        }
    }
}
