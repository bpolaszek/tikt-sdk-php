<?php

namespace Tikt\Website;
use GuzzleHttp\Psr7\Request;

class Registration {
  const DEFAULT_ATTRIBUTES = [
    'type' => 'Male',
    'country' => 'FR'
  ];
  const REQUEST_BODY_ATTRIBUTES_MAP = [
    'type' => 'user.type',
    'email' => 'user.email',
    'password' => 'user.password',
    'first_name' => 'user.first_name',
    'birthday' => 'user.birthday',
    'postal_code' => 'location.postal_code',
    'country' => 'location.country'
  ];
  const ALLOWED_TRACKERS = ['t1', 't2'];
  const DEFAULT_HEADERS = [
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
    'User-Agent' => "tikt-sdk-php/" . \Tikt::VERSION
  ];
  const SITE_SEGMENT_SOFT = 1;
  const SITE_SEGMENT_SEXY = 2;
  const SITE_SEGMENT_HARD = 3;
  private $client = null;
  private $attributes = null;
  private $parameters = null;

  public function __construct($attributes = []) {
    $this->parameters = new \ArrayObject();
    $this->attributes = new \ArrayObject(self::DEFAULT_ATTRIBUTES);
    $allowedAttributes = array_keys(self::REQUEST_BODY_ATTRIBUTES_MAP);
    foreach($attributes as $key => $value) {
      if (!in_array($key, $allowedAttributes)) {
        throw new \Exception(sprintf('attribute.%s.unknown', $key));
      }
      $this->attributes[$key] = $value;
    }
  }

  public function setTracker($name, $value) {
    if (!in_array($name, self::ALLOWED_TRACKERS)) {
      throw new Exception(sprintf('trackers.%s.unknown', $name));
    }
    $this->parameters[$name] = $value;
    return $this;
  }

  public function setAffiliate($id, $campaignId = null) {
    $this->parameters['ai'] = $id;
    if (!is_null($campaignId)) { $this->parameters['aci'] = $campaignId; }
    return $this;
  }

  public function setSiteSegment($max, $upper = false) {
    $this->parameters['sg'] = intval($max);
    $this->parameters['us'] = $upper == true ? 1 : 0;
    return $this;
  }

  public function setSite($id, $force = false) {
    $this->parameters['si'] = intval($id);
    $this->parameters['fs'] = $force == true ? 1 : 0;
    return $this;
  }

  public function getParameters() {
    return $this->parameters;
  }

  public function create() {
    $urlParameters = http_build_query($this->getParameters()->getArrayCopy());
    $requestBody = json_encode($this->buildRequestBody());
    $request = new Request(
      'POST', '/register?' . $urlParameters, self::DEFAULT_HEADERS, $requestBody
    );
    try {
      $response = $this->getClient()->send($request);
      return json_decode($response->getBody());
    } catch(\GuzzleHttp\Exception\ClientException $e) {
      throw new Registration\ResponseInvalidException($e->getResponse());
    }
  }

  private function getClient() {
    if (is_null($this->client)) {
      $options = [
        'base_uri' => \Tikt::getConfigValue('registration.endpoint'),
        'timeout' => 10.0
      ];
      if (!is_null(\Tikt::getConfigValue('http.proxy'))) {
        $options['proxy'] = \Tikt::getConfigValue('http.proxy');
      }
      $this->client = new \GuzzleHttp\Client($options);
    }
    return $this->client;
  }

  private function buildRequestBody() {
    $data = new \ArrayObject();
    foreach(
      $this->attributes as $attributeName => $attributeValue
    ) {
      if (!is_null(self::REQUEST_BODY_ATTRIBUTES_MAP[$attributeName])) {
        $requestAttributePath = self::REQUEST_BODY_ATTRIBUTES_MAP[$attributeName];
      } else {
        $requestAttributePath = sprintf('user.%s', $attributeName);
      }
      $hash = $data;
      $requestAttributeSplit = explode('.', $requestAttributePath);
      $requestAttributeName = array_pop($requestAttributeSplit);
      foreach($requestAttributeSplit as $namespace) {
        if (!isset($hash[$namespace])) { $hash[$namespace] = new \ArrayObject(); }
        $hash = $hash[$namespace];
      }
      if (isset($this->attributes[$attributeName])) {
        $hash[$requestAttributeName] = $attributeValue;
      }
    }
    return $data;
  }
}