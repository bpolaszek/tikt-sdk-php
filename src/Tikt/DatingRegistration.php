<?php

namespace Tikt;

class DatingRegistration implements \JsonSerializable {
  const ATTRIBUTES_NAMES = [
    'user', 'location', 'preferences'
  ];
  const ATTRIBUTES_CLASS_MAP = [
    'user' => 'UserAttribute',
    'location' => 'LocationAttribute',
    'preferences' => 'PreferencesAttribute'
  ];
  const HTTP_HEADERS = [
    'Content-Type' => 'application/json'
  ];
  const ALLOWED_TRACKERS = ['t1', 't2'];
  public $user = null;
  public $location = null;
  public $preferences = null;
  public $options = null;

  public function __construct($attributes = []) {
    foreach($attributes as $key => $value) {
      $this->writeAttribute($key, $value);
    }
  }

  public function setTracker($key, $value) {

  }

  public function setTrackers($trackers) {
    foreach($trackers as $key => $value) {
      if (!in_array($key, self::ALLOWED_TRACKERS)) {
        continue;
      }
      $this->writeOption($key, $value);
    }
    return $this;
  }

  public function setAffiliate($id, $campaignId = null) {
    $this->writeOption('ai', $id);
    if (!is_null($campaignId)) { $this->writeOption('aci', $campaignId); }
    return $this;
  }

  public function setSegment($segment, $upper = false) {
    $this->writeOption('sg', $segment);
    $this->writeOption('us', $upper == true ? 1 : 0);
    return $this;
  }

  public function setSite($id, $force = false) {
    $this->writeOption('si', $id);
    $this->writeOption('fs', $force == true ? 1 : 0);
    return $this;
  }

  public function writeAttribute($key, $value) {
    if (!in_array($key, self::ATTRIBUTES_NAMES)) {
      throw new AttributeNotFound($key);
    }
    $klass = sprintf(
      '\\Tikt\\DatingRegistration\\Attributes\\%s',
      self::ATTRIBUTES_CLASS_MAP[$key]
    );
    $this->{$key} = new $klass($value);
    return $this;
  }

  public function create() {
    $url = sprintf(
      '%s/register?%s',
      \Tikt::getConfigValue('registration_endpoint'),
      \http_build_query($this->getOptions()->getArrayCopy())
    );
    $response = \Requests::post($url, self::HTTP_HEADERS, json_encode($this));
    return $this->handleResponse($response);
  }

  public function getOptions() {
    if (is_null($this->options)) { $this->options = new \ArrayObject(); }
    return $this->options;
  }

  public function writeOption($key, $value) {
    $options = $this->getOptions();
    $options[$key] = $value;
    return $this;
  }

  public function asJSON() {
    return [
      'user' => $this->user->asJSON(),
      'location' => !is_null($this->location) ? $this->location->asJSON() : null,
      'preferences' => !is_null($this->preferences) ? $this->preferences->asJSON() : null
    ];
  }

  public function jsonSerialize() {
    return $this->asJSON();
  }

  private function handleResponse(\Requests_Response $response) {
    if (!in_array($response->status_code, [200, 201])) {
      throw new DatingRegistration\ResponseInvalidException($response);
    }
    return json_decode($response->body, false);
  }
}