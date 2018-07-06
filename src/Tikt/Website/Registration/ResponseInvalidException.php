<?php

namespace Tikt\Website\Registration;

/**
 * throwed when trying to set a Registration attribute that doesn't exists
 * @package tikt-sdk-php
 * @version 1.0
 */
class ResponseInvalidException extends \Exception {
  private $response = null;

  public function __construct(\GuzzleHttp\Psr7\Response $response) {
    $this->response = $response;
    $this->data = json_decode($response->getBody(), true);
    $this->message = isset($this->data['message']) ? $this->data['message'] : null;
  }

  public function getResponse() {
    return $this->response;
  }

  public function getData() {
    return $this->data;
  }
}