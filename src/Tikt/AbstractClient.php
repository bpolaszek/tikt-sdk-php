<?php

namespace Tikt;

class AbstractClient {
  const ALLOWED_STATUS_CODES = [200, 201, 204];
  const IAM1_HMAC_SHA256_TOKEN = 'IAM1-HMAC-SHA256 credentials=%s; date=%s; signature=%s';
  const SESSION_TOKEN_HEADER = 'Bearer %s';
  const DEFAULT_ENDPOINT = null;

  public $endpoint = null;
  public $accessKeyId = null;
  public $accessSecretKey = null;
  private $binaryAccessSecretKey = null;
  public $sessionToken = null;

  public function __construct($options = []) {
    $this->endpoint = isset($options['endpoint']) ? $options['endpoint'] : static::DEFAULT_ENDPOINT;
    $this->accessKeyId = isset($options['access_key_id']) ? $options['access_key_id'] : \Tikt::getConfigValue('access_key_id');
    $this->accessSecretKey = isset($options['access_secret_key']) ? $options['access_secret_key'] : \Tikt::getConfigValue('access_secret_key');
    $this->binaryAccessSecretKey = !is_null($this->accessSecretKey) ? $this->decodeSecretKey($this->accessSecretKey) : null;
    $this->sessionToken = isset($options['session_token']) ? $options['session_token'] : \Tikt::getConfigValue('session_token');
  }

  public function executeAction(
    $method = \Requests::GET, $actionName, $data = null, $options = []
  ) {
    $headers = $this->buildHeaders(
      isset($options['headers']) ? $options['headers'] : []
    );
    $response = \Requests::request(
      $this->getActionUrl($actionName), $headers, $data, $method, $options
    );
    if (!in_array($response->status_code, self::ALLOWED_STATUS_CODES)) {
      throw new AbstractClient\ResponseError($response);
    }
    return new AbstractClient\Response($response);
  }

  public function executeGetAction($actionName, $params = [], $options = []) {
    return $this->executeAction(\Requests::GET, $actionName, $params, $options);
  }

  public function executePostAction($actionName, $data, $options = []) {
    return $this->executeAction(\Requests::POST, $actionName, $data, $options);
  }

  private function getActionUrl($actionName) {
    return sprintf('%s/?action=%s', $this->endpoint, $actionName);
  }

  private function getRequestToken() {
    if (!is_null($this->sessionToken)) {
      return sprintf(SESSION_TOKEN_HEADER, $this->sessionToken);
    }
    $date = date('c');
    $kDate = hash_hmac('sha256', $date, $this->binaryAccessSecretKey, true);
    $kSign = hash_hmac('sha256', 'iam_request_v1', $kDate);
    return sprintf(
      self::IAM1_HMAC_SHA256_TOKEN, $this->accessKeyId, $date, $kSign
    );
  }

  public function buildHeaders($defaultHeaders = []) {
    return array_merge($defaultHeaders, [
      'Accept' => 'application/json',
      'Authorization' => $this->getRequestToken()
    ]);
  }

  function decodeSecretKey($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }
}