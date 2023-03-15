<?php

namespace YesTicket;

use WP_Error;
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
     * @return CachedImage|WP_Error image
     */
    public function getEventImage($event_id)
    {
        $yesTicketImageUrl = $this->getYesTicketUrlOfImage($event_id);

        return $this->cache->getFromCacheOrFresh($yesTicketImageUrl, function ($yesTicketImageUrl) {
            $content_type = $this->determineContentTypeOfImage($yesTicketImageUrl);
            return $this->_getEventImage($yesTicketImageUrl, $content_type);
        });
    }

    private function _getEventImage($yesTicketImageUrl, $content_type) {
        if ($content_type === "image/jpeg" || $content_type === "image/jpg") {
            $image = \imagecreatefromjpeg($yesTicketImageUrl);
            if (!$image) {
                \ytp_info(__FILE__, __LINE__, "Could not get '$yesTicketImageUrl' [$content_type] from YesTicket.");
                return new WP_Error(503);
            }
            \ob_start();
            if (!\imagejpeg($image, null, 100)) {
                $msg = \ob_get_clean();
                \ytp_info(__FILE__, __LINE__, "Could not render image data as [$content_type] from '$yesTicketImageUrl'. >> $msg");
                return new WP_Error(503, $msg);
            }
            return new CachedImage($content_type, \ob_get_clean());
        }
        if ($content_type === "image/png") {
            $image = \imagecreatefrompng($yesTicketImageUrl);
            if (!$image) {
                \ytp_info(__FILE__, __LINE__, "Could not get '$yesTicketImageUrl' [$content_type] from YesTicket.");
                return new WP_Error(503);
            }
            \ob_start();
            if (!\imagepng($image, null, 0)) {
                $msg = \ob_get_clean();
                \ytp_info(__FILE__, __LINE__, "Could not render image data as [$content_type] from '$yesTicketImageUrl'. >> $msg");
                return new WP_Error(503, $msg);
            }
            return new CachedImage($content_type, \ob_get_clean());
        }
        \ytp_info(__FILE__, __LINE__, "Unknown content-type [$content_type] found for resource '$yesTicketImageUrl'.");
        return new WP_Error(503);
    }

    public function determineContentTypeOfImage($yesTicketImageUrl)
    {
        $http = new \WP_Http();
        $result = $http->request($yesTicketImageUrl, array("method" => "HEAD"));
        if (\is_wp_error($result)) {
            \ytp_info(__FILE__, __LINE__, $result);
            return $result;
        }
        if (!\array_key_exists("response", $result) || !\array_key_exists("headers", $result)) {
            \ytp_info(__FILE__, __LINE__, "Malformed response for '$yesTicketImageUrl' >> \n" . \print_r($result, true));
            return new WP_Error(503);
        }
        $response = $result["response"];
        if (!\array_key_exists("code", $response)) {
            \ytp_info(__FILE__, __LINE__, "Result of call '$yesTicketImageUrl' has no response code. Response is: >> \n" . \print_r($response, true));
            return new WP_Error(503);
        }
        $status_code = $response["code"];
        if ($status_code != 200) {
            \ytp_info(__FILE__, __LINE__, "Response code of call '$yesTicketImageUrl' is not 200 [actual: $status_code]");
            return new WP_Error(503);
        }
        $headers = $result["headers"]->getAll();
        if (!\array_key_exists("content-type", $headers)) {
            \ytp_info(__FILE__, __LINE__, "Result of call '$yesTicketImageUrl' has no content-type header. Available headers are: >> \n" . \print_r($headers, true));
            return new WP_Error(503);
        }
        $content_type = $headers["content-type"];
        if (\stripos($content_type, "image") === false) {
            \ytp_info(__FILE__, __LINE__, "Content-type of resource '$yesTicketImageUrl' is not an image [actual: $content_type]");
            return new WP_Error(503);
        }
        return $content_type;
    }

    public function getYesTicketUrlOfImage($event_id)
    {
        return "https://www.yesticket.org/dev/picture.php?event=" . $event_id;
    }
}
