<?php

/**
 * Base class for anything that uses Templates
 */
abstract class YesTicketTemplater
{

  /**
   * Path to the DIR containing the templates.
   *
   * @var string
   */
  protected $template_path;

  /**
   * Constructor
   *
   * @param string $template_path to the directory containing the templates
   */
  protected function __construct($template_path)
  {
    $this->template_path = rtrim($template_path, '/');
  }

  /**
   * Renders the given template, if it's readable.
   *
   * @param string $template
   * @param array $variables passed via 'compact', to be used via 'extract'
   */
  protected function render_template($template, $variables = array())
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
}
