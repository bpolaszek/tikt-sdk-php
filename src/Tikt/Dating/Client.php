<?php

namespace Tikt\Dating;

class Client extends \Tikt\AbstractClient {
  const DEFAULT_ENDPOINT = 'https://api.dating.tikt.net';

  public function partnerDescribeUser($params = []) {
    $response = $this->executeGetAction('PartnerDescribeUser', $params);
    return $response->getData();
  }
}