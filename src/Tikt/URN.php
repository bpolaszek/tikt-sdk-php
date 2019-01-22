<?php

namespace Tikt;

class URN {
  const URN_REGEXP = '/^urn:([^:]*):([^:]*):([^:]*):([^:]*):([^:\/]+)[:\/]?(.+)?$/i';

  public $partition = null;
  public $service = null;
  public $region = null;
  public $account = null;
  public $resourceType = null;
  public $resourceId = null;

  static public function parse($value) {
    $matches = [];
    preg_match(self::URN_REGEXP, $value, $matches);
    if (count($matches) < 7) {
      throw new \Tikt\URN\ProvidedURNInvalidException($value);
    }
    return new self([
      'partition' => $matches[1],
      'service' => $matches[2],
      'region' => $matches[3],
      'account' => $matches[4],
      'resource_type' => $matches[5],
      'resource_id' => $matches[6],
    ]);
  }

  public function __construct($attributes = []) {
    $this->partition = isset($attributes['partition']) ? $attributes['partition'] : 'tikt';
    $this->service = isset($attributes['service']) ? $attributes['service'] : null;
    $this->region = isset($attributes['region']) ? $attributes['region'] : null;
    $this->account = isset($attributes['account']) ? $attributes['account'] : null;
    $this->resourceType = isset($attributes['resource_type']) ? $attributes['resource_type'] : null;
    $this->resourceId = isset($attributes['resource_id']) ? $attributes['resource_id'] : null;
  }

  public function getPartition() {
    return $this->partition;
  }

  public function getService() {
    return $this->service;
  }

  public function getRegion() {
    return $this->region;
  }

  public function getAccount() {
    return $this->account;
  }

  public function getResourceType() {
    return $this->resourceType;
  }

  public function getResourceId() {
    return $this->resourceId;
  }
}
