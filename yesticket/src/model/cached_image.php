<?php

namespace YesTicket\Model;

class CachedImage implements \Serializable
{
  /**
   * @var string
   */
  private $content_type;

  /**
   * @var string
   */
  private $image_data;

  /**
   * @param string $content_type
   * @param string $image_data
   */
  public function __construct($content_type = null, $image_data = null)
  {
    $this->content_type = $content_type;
    if (!empty($image_data)) {
      $this->set_image_data($image_data);
    }
  }

  public function set_image_data($image_data)
  {
    $this->image_data = \base64_encode($image_data);
  }

  public function get_image_data()
  {
    return \base64_decode($this->image_data);
  }

  public function set_content_type($content_type)
  {
    $this->content_type = $content_type;
  }

  public function get_content_type()
  {
    return $this->content_type;
  }

  public function __serialize()
  {
    return \json_encode([
      'content_type' => $this->content_type,
      'image_data' => $this->image_data,
    ]);
  }

  public function serialize()
  {
    return \json_encode([
      'content_type' => $this->content_type,
      'image_data' => $this->image_data,
    ]);
  }

  public function __unserialize($data)
  {
    $decoded = \json_decode($data, true);
    $this->content_type = $decoded['content_type'];
    $this->image_data = $decoded['image_data'];
  }

  public function unserialize($data)
  {
    $decoded = \json_decode($data, true);
    $this->content_type = $decoded['content_type'];
    $this->image_data = $decoded['image_data'];
  }
}
