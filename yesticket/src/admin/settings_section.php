<?php

namespace YesTicket\Admin;

use \YesTicket\Templater;

include_once(__DIR__ . "/../helpers/functions.php");
include_once(__DIR__ . "/../helpers/templater.php");

/**
 * Base class for Settings
 */
abstract class SettingsSection extends Templater
{

  /**
   * Slug of the parent menu entry
   *
   * @var string
   */
  private $parent_slug;

  /**
   * Constructor.
   *
   * @param string $parent_slug for the menu hirarchy
   */
  public function __construct($parent_slug)
  {
    parent::__construct(__DIR__ . '/templates');
    $this->parent_slug = $parent_slug;
  }

  /**
   * Get the parent slug of the admin page.
   *
   * @return string
   */
  public function get_parent_slug()
  {
    return $this->parent_slug;
  }

  /**
   * Get the slug used by the admin page.
   *
   * @return string
   */
  public function get_slug()
  {
    return $this->parent_slug . '-settings';
  }

  /**
   * Return html for success message
   * 
   * @param string $msg the message content
   * 
   * @return string html for success message
   */
  function success_message($msg)
  {
    return "<p class='ytp-admin-success'>$msg</p>";
  }
}
