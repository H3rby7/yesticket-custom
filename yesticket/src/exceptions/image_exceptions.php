<?php

namespace YesTicket;

use RuntimeException;

class ImageException extends RuntimeException {
  protected $code = '500';
}

class ImageNotFoundException extends ImageException {
  protected $code = '404';
}

class WrongImageTypeException extends ImageException {
  
}