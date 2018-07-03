<?php

namespace Tikt\AbstractClient;

class Response {
  private $response = null;

  public function __construct(\Requests_Response $response) {
    $this->code = $response->status_code;
    $this->data = json_decode($response->body, true);
    $this->context = $response;
  }

  public function getBody() {
    return $response->body;
  }
}