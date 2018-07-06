<?php

namespace Tikt\DatingRegistration;

/**
 * throwed when trying to set a Registration attribute that doesn't exists
 * @package tikt-sdk-php
 * @version 1.0
 */
class AttributeNotFoundException extends Exception {
  private $attributeName = null;

  public function __construct(string $attributeName) {
    $this->attributeName = $attributeName;
  }

  public function getAttributeName() {

  }
}