<?php

namespace YesTicket\Model;

class CachedImage implements \Serializable
{
  /**
   * @var string
   */
  public $content_type;

  /**
   * @var string
   */
  public $image_data;

  /**
   * @param string $content_type
   * @param string $image_data
   */
  public function __construct($content_type, $image_data)
  {
    $this->content_type = $content_type;
    $this->image_data = $image_data;
  }

  public function getHeader()
  {
    return "Content-Type: " . $this->content_type;
  }

  public function __serialize()
  {
    return [
      'content_type' => $this->content_type,
      'image_data' => $this->image_data,
    ];
  }

  public function serialize()
  {
    return \serialize([
      $this->content_type,
      $this->image_data,
    ]);
  }

  public function __unserialize($data)
  {
    $this->content_type = $data['content_type'];
    $this->image_data = $data['image_data'];
  }

  public function unserialize($image_data)
  {
    list(
      $this->content_type,
      $this->image_data,
    ) = \unserialize($image_data);
  }
}
