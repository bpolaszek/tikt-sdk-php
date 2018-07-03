<?php

require 'vendor/autoload.php';

Tikt::configure([
  // 'access_key_id' => $_ENV['TIKT_ACCESS_KEY_ID'],
  // 'access_secret_key' => $_ENV['TIKT_ACCESS_SECRET_KEY'],
  'registration_endpoint' => 'https://api.website.tikt.net'
]);

$registration = new Tikt\Registration([
  'user' => [
    'type' => 'Male',
    'email' => 'phptest03@soond.com',
    'first_name' => 'Julius',
    'birthday' => '1991-02-01'
  ],
  'location' => [
    'postal_code' => '26000'
  ]
]);

try {
  $result = $registration->create();
  var_dump($result->redirect_url);
} catch(Tikt\Registration\ResponseInvalidException $e) {
  var_dump($e->getData());
}