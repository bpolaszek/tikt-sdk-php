<?php

namespace Tikt\Dating;

class Client extends \Tikt\AbstractClient {
  const DEFAULT_ENDPOINT = 'https://api.dating.tikt.net';

  const ACTIONS = [
    'DescribeWebsite' => [ 'method' => 'GET' ],
    'PartnerDescribeUser' => [ 'method' => 'GET' ]
  ];
}
