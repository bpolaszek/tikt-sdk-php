<?php

namespace Tikt\DatingRegistration\Attributes;

class UserAttribute extends BaseAttribute {
  public function setBirthday($value) {
    if ($value instanceof \DateTime) {
      $this->birthday = $value;
      return;
    }
    $this->birthday = new \DateTime($value);
  }

  public function setFirstName($value) {
    $this->firstName = $value;
  }
}