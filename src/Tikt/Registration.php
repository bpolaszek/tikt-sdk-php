<?php

namespace Tikt;

class Registration implements \JsonSerializable {
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
  public $user = null;
  public $location = null;
  public $preferences = null;

  public function __construct($attributes = []) {
    foreach($attributes as $key => $value) {
      $this->writeAttribute($key, $value);
    }
  }

  public function writeAttribute($key, $value) {
    if (!in_array($key, self::ATTRIBUTES_NAMES)) {
      throw new AttributeNotFound($key);
    }
    $klass = '\\Tikt\\Registration\\Attributes\\' . self::ATTRIBUTES_CLASS_MAP[$key];
    $this->{$key} = new $klass($value);
  }

  public function create() {
    $url = sprintf(
      '%s/register', \Tikt::getConfigValue('registration_endpoint')
    );
    $response = \Requests::post($url, self::HTTP_HEADERS, json_encode($this));
    return $this->handleResponse($response);
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
      throw new Registration\ResponseInvalidException($response);
    }
    return json_decode($response->body, false);
  }
}