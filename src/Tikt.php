<?php

class Tikt {
  const DEFAULT_REGISTRATION_ENDPOINT = 'https://api.website.tikt.net';
  static private $configuration = [
    'access_key_id' => null,
    'access_secret_key' => null,
    'session_token' => null,
    'registration_endpoint' => self::DEFAULT_REGISTRATION_ENDPOINT
  ];

  static public function configure($configuration) {
    self::$configuration = array_merge(self::$configuration, $configuration);
  }

  static public function getConfigValue($name) {
    if (!isset(self::$configuration[$name])) {
      return null;
    }
    return self::$configuration[$name];
  }
}