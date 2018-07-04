<?php

namespace Tikt\Affiliate;

class Client extends \Tikt\AbstractClient {
  const DEFAULT_ENDPOINT = 'https://api.affiliate.tikt.net';

  public function listPayoutsByDate($params = []) {
    $response = $this->executeGetAction('ListPayoutsByDate', $params);
    return $response->getData();
  }
}