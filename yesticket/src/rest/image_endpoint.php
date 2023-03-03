<?php

namespace YesTicket\Rest;

use \YesTicket\ImageApi;

include_once(__DIR__ . "/../helpers/image_api.php");
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

  /**
   * The $instance
   *
   * @var ImageApi
   */
  private $api;

  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->api = ImageApi::getInstance();
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
          'validate_callback' => [$this, 'validationCallback']
        ),
      ),
    ));
  }

  public function validationCallback($param, $request = null, $key = null)
  {
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

  public function handleRequest($data)
  {
    try {
      $result = $this->api->getEventImage($data['event_id']);
      \header($result->getHeader(), true, 200);
      echo $result->image_data;
      return new \WP_REST_Response($result->image_data, 200, [$result->getHeader()]);
    } catch (\Exception $e) {
      $msg = $e->getMessage();
      $code = $e->getCode();
      \ytp_log(__FILE__ . "@" . __LINE__ . ": ERROR $code > '$msg'");
      return new \WP_Error($code, $msg, array('status' => $code));
    }
  }
}
