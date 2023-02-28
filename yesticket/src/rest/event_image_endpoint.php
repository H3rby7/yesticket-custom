<?php

namespace YesTicket\Rest;

use \YesTicket\ImageCache;

include_once(__DIR__ . "/../helpers/image_cache.php");
include_once(__DIR__ . "/../helpers/functions.php");

add_action('rest_api_init', function () {
  ImageEndpoint::getInstance()->registerRoute();
});

class ImageEndpoint
{

  /**
   * The $instance
   *
   * @var ImageEndpoint
   */
  static private $instance;

  /**
   * Get the $instance
   * 
   * @return ImageEndpoint $instance
   */
  static public function getInstance()
  {
    if (!isset(ImageEndpoint::$instance)) {
      ImageEndpoint::$instance = new ImageEndpoint();
    }
    return ImageEndpoint::$instance;
  }

  public function registerRoute()
  {
    // 127.0.0.1/wp-json/yesticket/v1/picture/123
    \register_rest_route('yesticket/v1', '/picture/(?P<event_id>\d+)', array(
      'methods' => 'GET',
      'callback' => [$this, 'handleRequest'],
      'permission_callback' => function () {
        return true;
      },
      'args' => array(
        'event_id' => array(
          'validate_callback' => function ($param, $request, $key) {
            if (!is_numeric($param) || $param < 1) {
              return false;
            }
            if (((int)$param != $param)) {
              // is not a whole number (e.G. 32.5 = true; 32 = false)
              return false;
            }
            // \header('Content-Type: image/jpeg', true);
            return true;
          }
        ),
      ),
    ));
  }

  public function handleRequest($data)
  {
    // https://www.yesticket.org/dev/picture.php?event=4573
    $yesTicketImageUrl = "https://www.yesticket.org/dev/picture.php?event=" . $data['event_id'];
    $result = ImageCache::getInstance()->getFromCacheOrFresh($yesTicketImageUrl);
    \header('Content-Type: image/jpeg', true);
    \imagejpeg($result);
    return null;
  }
}
