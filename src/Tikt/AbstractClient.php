<?php

namespace Tikt;
use GuzzleHttp\Psr7\Request;

class AbstractClient {
  const ALLOWED_STATUS_CODES = [200, 201, 204];
  const IAM1_HMAC_SHA256_TOKEN = 'IAM1-HMAC-SHA256 credentials=%s; date=%s; signature=%s';
  const SESSION_TOKEN_HEADER = 'Bearer %s';
  const DEFAULT_ENDPOINT = null;
  const ACTIONS = [];

  public $endpoint = null;
  public $accessKeyId = null;
  public $accessSecretKey = null;
  private $binaryAccessSecretKey = null;
  public $sessionToken = null;
  private $client = null;

  static public function getAvailableActions() {
    return static::ACTIONS;
  }

  public function __construct($options = []) {
    $this->endpoint = isset($options['endpoint']) ? $options['endpoint'] : static::DEFAULT_ENDPOINT;
    $this->accessKeyId = isset($options['access_key_id']) ? $options['access_key_id'] : \Tikt::getConfigValue('access_key_id');
    $this->accessSecretKey = isset($options['access_secret_key']) ? $options['access_secret_key'] : \Tikt::getConfigValue('access_secret_key');
    $this->binaryAccessSecretKey = !is_null($this->accessSecretKey) ? $this->decodeSecretKey($this->accessSecretKey) : null;
    $this->sessionToken = isset($options['session_token']) ? $options['session_token'] : \Tikt::getConfigValue('session_token');
  }

  public function __call($name, $arguments) {
    $actionName = ucfirst($name);
    if (isset(static::ACTIONS[$actionName])) {
      $actionMethod = static::ACTIONS[$actionName]['method'];
      return $this->executeAction($actionMethod, $actionName, $arguments[0]);
    }
    throw new \Exception(sprintf('action %s does not exists', $name));
  }

  public function executeAction(
    $method = 'GET', $actionName, $data = null, $options = []
  ) {
    $headers = $this->buildHeaders(
      isset($options['headers']) ? $options['headers'] : []
    );
    if ($method == 'GET') {
      $urlParameters = array_merge([], $data, [ 'action' => $actionName ]);
      $data = null;
    } else {
      $urlParameters = [ 'action' => $actionName ];
      $data = json_encode($data);
    }
    $request = new Request(
      $method, '/?' . \http_build_query($urlParameters), $headers, $data
    );

    try {
      $response = $this->getClient()->send($request);
      return json_decode($response->getBody(), true);
    } catch(\GuzzleHttp\Exception\ClientException $e) {
      throw new AbstractClient\ResponseError($e->getResponse());
    }
  }

  public function executeGetAction($actionName, $params = [], $options = []) {
    return $this->executeAction('GET', $actionName, $params, $options);
  }

  public function executePostAction($actionName, $data, $options = []) {
    return $this->executeAction('POST', $actionName, $data, $options);
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

  private function decodeSecretKey($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }

  private function getClient() {
    if (is_null($this->client)) {
      $options = [
        'base_uri' => $this->endpoint,
        'timeout' => 10.0
      ];
      if (!is_null(\Tikt::getConfigValue('http.proxy'))) {
        $options['proxy'] = \Tikt::getConfigValue('http.proxy');
      }
      $this->client = new \GuzzleHttp\Client($options);
    }
    return $this->client;
  }

}
