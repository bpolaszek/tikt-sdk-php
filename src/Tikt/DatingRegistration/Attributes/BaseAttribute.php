<?php

namespace Tikt\DatingRegistration\Attributes;

class BaseAttribute implements \JsonSerializable {
  public function __construct($options = []) {
    foreach($options as $key => $value) {
      $this->set($key, $value);
    }
  }

  public function set($name, $value) {
    $propertyName = $this->camelize($name);
    $methodName = 'set' . $propertyName;
    if (method_exists($this, $methodName)) {
      \call_user_func([$this, $methodName], $value);
    }
    else {
      $this->{$propertyName} = $value;
    }
  }

  public function get($name) {
    $propertyName = $this->camelize($name);
    $methodName = 'get' . $propertyName;
    if (method_exists($this, $methodName)) {
      return \call_user_func([$this, $methodName]);
    }
    else {
      return $this->{$propertyName};
    }
  }

  public function asJSON() {
    $attributes = \get_object_vars($this);
    $json = [];
    foreach($attributes as $key => $value) {
      $value = $this->get($key);
      $json_key = $this->underscore($key);
      if ($value instanceof \DateTime) {
        $value = $value->format('c');
      }
      $json[$json_key] = $value;
    }
    return $json;
  }

  public function jsonSerialize() {
    return $this->asJSON();
  }

  private function camelize($str, $capitalise_first_char = false) {
    if ($capitalise_first_char) { $str[0] = strtoupper($str[0]); }
    return preg_replace_callback(
      '/_([a-z])/', function($c) { return strtoupper($c[1]); }, $str
    );
  }

  private function underscore($input) {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
  }
}