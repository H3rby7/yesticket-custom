<?php

namespace YesTicket;

use \WP_Error;
use \GdImage;
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
   * @return CachedImage|WP_Error image (using cache) or ERROR. Error's data will be the resource URL.
   */
  public function getEventImage($event_id)
  {
    $yesTicketImageUrl = $this->getYesTicketUrlOfImage($event_id);
    $image = $this->cache->getFromCacheOrFresh($yesTicketImageUrl, function ($get_url) {
      return $this->_getEventImage($get_url);
    });
    if (\is_wp_error($image)) {
      $image->add_data($yesTicketImageUrl);
    }
    return $image;
  }

  /**
   * Render image from $get_url
   * 
   * @param string $get_url of the image
   * 
   * @return CachedImage|WP_Error the image wrapped in the cacheable object or ERROR.
   */
  private function _getEventImage($get_url)
  {
    $content_type = $this->determineContentTypeOfImage($get_url);
    if (\is_wp_error($content_type)) {
      return $content_type;
    }
    $image = $this->imageCreateFrom($content_type, $get_url);
    if (\is_wp_error($image)) {
      return $image;
    }
    return $this->imageRender($content_type, $image);
  }

  /**
   * Make a 'HEAD' request to the given resource.
   * Ensuring it is a form of image
   * 
   * @param string $yesTicketImageUrl URL of the resource
   * @return string|WP_Error the content-type or ERROR
   */
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

  /**
   * Get image data from $url using $content_type
   * 
   * @param string $content-type of the resource ('image/xyz')
   * @param string $url of the resource
   * 
   * @return resource|GdImage|WP_Error the image data or ERROR.
   */
  private function imageCreateFrom($content_type, $url)
  {
    $image = false;
    if ($content_type === "image/jpeg" || $content_type === "image/jpg") {
      $image = \imagecreatefromjpeg($url);
    } else if ($content_type === "image/png") {
      $image = \imagecreatefrompng($url);
    }
    if (!$image) {
      \ytp_info(__FILE__, __LINE__, "Could not get '$url' [$content_type] from YesTicket.");
      return new WP_Error(503);
    }
    return $image;
  }

  /**
   * Render $image as given $content_type
   * @param string $content-type of the resource ('image/xyz')
   * @param resource|GdImage $image data
   * 
   * @return CachedImage|WP_Error the image wrapped in the cacheable object or ERROR.
   */
  private function imageRender($content_type, $image)
  {
    $rendering_worked = false;
    \ob_start();
    if ($content_type === "image/jpeg" || $content_type === "image/jpg") {
      $rendering_worked = \imagejpeg($image, null, 100);
    } else if ($content_type === "image/png") {
      $rendering_worked = \imagepng($image, null, 0);
    }
    if (!$rendering_worked) {
      $msg = \ob_get_clean();
      \ytp_info(__FILE__, __LINE__, "Could not render image data as [$content_type]. >> $msg");
      return new WP_Error(503, $msg);
    }
    return new CachedImage($content_type, \ob_get_clean());
  }

  /**
   * Takes an event_id and returns the corresponding event image's URL on the yesticket.org platform
   * 
   * @param number $event_id ID of event
   * @return string image url
   */
  public function getYesTicketUrlOfImage($event_id)
  {
    return "https://www.yesticket.org/picture.php?event=" . $event_id;
  }
}
