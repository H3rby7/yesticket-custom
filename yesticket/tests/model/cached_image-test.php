<?php

namespace YesTicket\Model;

use YesTicket\Model\CachedImage;

class CachedImageTest extends \WP_UnitTestCase
{
  function test_class_exists()
  {
    $this->assertTrue(\class_exists("YesTicket\Model\CachedImage"));
  }

  /**
   * @covers YesTicket\Model\CachedImage
   */
  function test_serialize_deserialize()
  {
    $imageToSerialize = \getCachedImage('image/jpeg', '\imagejpeg', 100);
    $unserializedImage = new CachedImage();
    $unserializedImage->unserialize($imageToSerialize->serialize());
    $this->assertSame($imageToSerialize->get_content_type(), $unserializedImage->get_content_type());
    $this->assertSame($imageToSerialize->get_image_data(), $unserializedImage->get_image_data());
  }

  /**
   * @covers YesTicket\Model\CachedImage
   */
  function test___serialize___deserialize()
  {
    $imageToSerialize = \getCachedImage('image/jpeg', '\imagejpeg', 100);
    $unserializedImage = new CachedImage();
    $unserializedImage->__unserialize($imageToSerialize->__serialize());
    $this->assertSame($imageToSerialize->get_content_type(), $unserializedImage->get_content_type());
    $this->assertSame($imageToSerialize->get_image_data(), $unserializedImage->get_image_data());
  }

  /**
   * @covers YesTicket\Model\CachedImage
   */
  function test_can_be_transiented()
  {
    $image = \getCachedImage('image/jpeg', '\imagejpeg', 100);
    $this->assertTrue(\set_transient('test', $image->serialize()), "Should be able to cache");
    $fromCache = new CachedImage();
    $fromCache->unserialize(\get_transient('test'));
    $this->assertNotEmpty($fromCache);
    $this->assertSame($image->get_content_type(), $fromCache->get_content_type());
    $this->assertSame($image->get_image_data(), $fromCache->get_image_data());
  }

}
