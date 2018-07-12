<?php

namespace Tikt\Affiliate;

class Client extends \Tikt\AbstractClient {
  const DEFAULT_ENDPOINT = 'https://api.affiliate.tikt.net';

  const ACTIONS = [
    'ListPayoutsByDate' => [ 'method' => 'GET' ],
    'ListLeadsByDate' => [ 'method' => 'GET' ]
  ];
}
