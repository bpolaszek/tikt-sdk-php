<?php

namespace Tikt\Website;
use GuzzleHttp\Psr7\Request;

class Registration {
  const DEFAULT_ATTRIBUTES = [
    'type' => 'Male',
    'country' => 'FR'
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
    foreach($attributes as $key => $value) {
      $this->attributes[$key] = $value;
    }
  }

  /**
   * set a tracker value, current allowed trackers are:
   *  - t1
   *  - t2
   * t1 + t2 values size should not exceed 4 KB
   * @param string $name Tracker name
   * @param string $value Tracker value
   * @return self
   */
  public function setTracker($name, $value) {
    if (!in_array($name, self::ALLOWED_TRACKERS)) {
      throw new Exception(sprintf('trackers.%s.unknown', $name));
    }
    $this->parameters[$name] = $value;
    return $this;
  }

  /**
   * set your affiliate details
   * @param int $id Your affiliate ID
   * @param string|null $campaignId Free text input that allows you to group your leads
   */
  public function setAffiliate($id, $campaignId = null) {
    $this->parameters['ai'] = $id;
    if (!is_null($campaignId)) { $this->parameters['aci'] = $campaignId; }
    return $this;
  }

  /**
   * define the maximum segment that is allowed when selecting targetted website
   * $upper boolean allowed the website selector to fallback on a higher segment
   * if no websites have been with the provided $max segment
   * @param int $max Max segment
   * @param bool $upper true/false
   */
  public function setSiteSegment($max, $upper = false) {
    $this->parameters['sg'] = intval($max);
    $this->parameters['us'] = $upper == true ? 1 : 0;
    return $this;
  }

  /**
   * Force a specific website to be tried first, if force is false it will fallback
   * to the default website selection
   */
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
      'POST', '/partner/register?' . $urlParameters, self::DEFAULT_HEADERS,
      $requestBody
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
      $hash = $data;
      $attributeNameSplit = explode('.', $attributeName);
      $attributeKey = array_pop($attributeNameSplit);
      foreach($attributeNameSplit as $namespace) {
        if (!isset($hash[$namespace])) {
          $hash[$namespace] = new \ArrayObject();
        }
        $hash = $hash[$namespace];
      }
      $hash[$attributeKey] = $attributeValue;
    }
    return $data;
  }
}