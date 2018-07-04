<?php

namespace Tikt\AbstractClient;

class Response {
  private $response = null;

  public function __construct(\Requests_Response $response) {
    $this->code = $response->status_code;
    $this->data = json_decode($response->body, true);
    $this->context = $response;
  }

  public function __get($key) {
    var_dump($this->data);
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
    return $response->body;
  }
}