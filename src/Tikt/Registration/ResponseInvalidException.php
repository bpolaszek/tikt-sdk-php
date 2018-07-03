<?php

namespace Tikt\Registration;

/**
 * throwed when trying to set a Registration attribute that doesn't exists
 * @package tikt-sdk-php
 * @version 1.0
 */
class ResponseInvalidException extends Exception {
  private $response = null;

  public function __construct(\Requests_Response $response) {
    $this->response = $response;
    $this->data = json_decode($response->body, true);
    $this->message = $this->data['message'];
  }

  public function getResponse() {
    return $this->response;
  }

  public function getData() {
    return $this->data;
  }
}