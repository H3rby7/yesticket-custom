<?php

namespace YesTicket;

use \YesTicket\ImageCache;
use \InvalidArgumentException;

include_once("cache.php");
include_once("functions.php");
include_once("plugin_options.php");
include_once(__DIR__ . "/../model/event.php");

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


}
