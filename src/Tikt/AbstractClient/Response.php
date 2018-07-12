<?php

namespace Tikt\AbstractClient;

class Response {
  private $response = null;

  public function __construct(\GuzzleHttp\Psr7\Response $response) {
    $this->code = $response->getStatusCode();
    $this->data = json_decode($response->getBody(), true);
    $this->context = $response;
  }

  public function __get($key) {
    if (isset($this->data[$key])) {
      return $this->data[$key];
    }
    return parent::__get($key);
  }

  public function getCode() {
    return $this->code;
  }

  public function getData() {
    return $this->data;
  }

  public function getBody() {
    return $response->getBody();
  }
}
