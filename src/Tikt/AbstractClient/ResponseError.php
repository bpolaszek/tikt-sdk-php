<?php

namespace Tikt\AbstractClient;

class ResponseError extends \Exception {
  private $response = null;

  public function __construct($response) {
    $this->response = $response;
  }

  public function getContext() {
    return $this->response;
  }
}