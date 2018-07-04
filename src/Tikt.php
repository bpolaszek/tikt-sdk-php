<?php

class Tikt {
  const DEFAULT_REGISTRATION_ENDPOINT = 'https://api.website.tikt.net';
  static private $configuration = null;

  static public function configure($configuration) {
    self::$configuration = array_merge(self::configuration(), $configuration);
  }

  static public function configuration() {
    if (is_null(self::$configuration)) {
      self::$configuration = [
        'access_key_id' => getenv('TIKT_ACCESS_KEY_ID') !== false ? getenv('TIKT_ACCESS_KEY_ID') : null,
        'access_secret_key' => getenv('TIKT_ACCESS_SECRET_KEY') !== false ? getenv('TIKT_ACCESS_SECRET_KEY') : null,
        'session_token' => getenv('TIKT_SESSION_TOKEN') !== false ? getenv('TIKT_SESSION_TOKEN') : null,
        'registration_endpoint' => self::DEFAULT_REGISTRATION_ENDPOINT
      ];
    }
    return self::$configuration;
  }

  static public function getConfigValue($name, $default = null) {
    $config = self::configuration();
    if (!isset($config[$name])) {
      return $default;
    }
    return $config[$name];
  }
}