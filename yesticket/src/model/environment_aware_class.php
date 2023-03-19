<?php

namespace YesTicket\Model;

/**
 * Extend this class to add 'environment aware' capabilities to your model.
 */
abstract class EnvironmentAware
{

  /**
   * @var string The environment. '' for PROD
   */
  private $yesticket_environment = '';

  /**
   * @return true if $this->yesticket_environment is 'dev'
   */
  public function isDevEnvironment() {
    return !empty($this->yesticket_environment) && $this->yesticket_environment == 'dev';
  }

  /**
   * Set environment for yesticket.
   * Use '' for PROD.
   * @param string $environment
   * @return self
   */
  public function setYesticketEnvironment($environment) {
    $this->yesticket_environment = $environment;
    return $this;
  }

}
