<?php

namespace YesTicket\Rest;

use \YesTicket\ImageApi;
use YesTicket\Model\CachedImage;

include_once(__DIR__ . "/../helpers/image_api.php");
include_once(__DIR__ . "/../helpers/functions.php");

add_action('rest_api_init', function () {
  ImageEndpoint::getInstance()->registerRoute();
});

// https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
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
    \add_filter('rest_pre_serve_request', [$this, 'servePicture'], 10, 2);
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
      return new \WP_REST_Response($result, 200, ['Content-Type: ' . $result->content_type]);
    } catch (\Exception $e) {
      $msg = $e->getMessage();
      $code = $e->getCode();
      \ytp_log(__FILE__ . "@" . __LINE__ . ": ERROR $code > '$msg'");
      return new \WP_Error($code, $msg, array('status' => $code));
    }
  }

  /**
   * @param boolean $served
   * @param \WP_REST_Response $result
   * @param \WP_REST_Request $request
   * @param \WP_REST_Server $server
   */
  public function servePicture($served, $result)
  {
    if ($served || $result->is_error()) {
      return false;
    }
    if (\stripos($result->get_matched_route(), '/yesticket/v1/picture') === false) {
      return false;
    }
    if (!($result->get_data() instanceof CachedImage)) {
      \ytp_log(__FILE__ . "@" . __LINE__ . ": 'Result data is not of type CachedImage'");
      return false;
    }
    if (!\headers_sent()) {
      // @codeCoverageIgnoreStart
      foreach ($result->get_headers() as $header) {
        \header($header, true);
      }
      // @codeCoverageIgnoreEnd
    }
    echo $result->get_data()->image_data;
    return true;
  }
}
