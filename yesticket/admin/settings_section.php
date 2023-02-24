<?php

include_once(__DIR__ . "/../helpers/functions.php");

/**
 * Base class for YesTicketSettings
 */
abstract class YesTicketSettingsSection
{

  /**
   * Path to the example templates.
   *
   * @var string
   */
  protected $template_path;

  /**
   * Slug of the parent menu entry
   *
   * @var string
   */
  private $parent_slug;

  /**
   * Constructor.
   *
   * @param string $template_path
   */
  public function __construct($parent_slug, $template_path)
  {
    $this->parent_slug = $parent_slug;
    $this->template_path = rtrim($template_path, '/');
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
   * Renders the given template if it's readable.
   *
   * @param string $template
   */
  function render_template($template, $variables = array())
  {
    $template_path = $this->template_path . '/' . $template . '.php';

    if (!is_readable($template_path)) {
      ytp_log(__FILE__ . "@" . __LINE__ . ": 'Template not found: $template_path'");
      return;
    }
    // Extract the variables to a local namespace
    extract($variables);

    include $template_path;
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
