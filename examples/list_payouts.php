<?php

require 'vendor/autoload.php';

$affiliateClient = new Tikt\Affiliate\Client([
  'endpoint' => 'https://api.affiliate.tikt.net'
]);

$datingClient = new Tikt\Dating\Client([
  'endpoint' => 'https://api.dating.tikt.net'
]);

function formatDate($date, $format = 'Y-m-d H:m:s') {
  return (new DateTime($date))->format($format);
}

function calculateAmountTaxFree($amount, $tax = 20.0) {
  return $amount / (($tax / 100.0) + 1);
}

function findDatingUser($client, $productUrn) {
  try {
    $response = $client->partnerDescribeUser(
      [ 'user_id' => $productUrn->getResourceId() ]
    );
    return [$response['user'], $response['website']];
  } catch(Tikt\AbstractClient\ResponseError $e) {
    return [null, null];
  }
}

try {
  $response = $affiliateClient->listPayoutsByDate([
    'account_id' => 30200,
    'from_date' => '2016-01-01',
    'to_date' => '2018-12-12',
    'limit' => 10
  ]);

  foreach($response['payouts'] as $payout) {
    $productUrn = \Tikt\URN::parse($payout['product_urn']);
    $plan = isset($payout['data']['plan']) ? $payout['data']['plan'] : null;

    list($user, $website) = findDatingUser($datingClient, $productUrn);

    $record = [
      'id' => $payout['id'],
      'id_abonnement' => $user['id'],
      'date' => formatDate($payout['created_at']),
      'trackers' => [
        't1' => isset($payout['trackers']['tracker1']) ? $payout['trackers']['tracker1'] : null,
        't2' => isset($payout['trackers']['tracker2']) ? $payout['trackers']['tracker2'] : null
      ],
      'id_produit' => $productUrn->getService(),
      'id_offre' => !is_null($plan) ? $plan['name'] : null,
      'type_transaction' => $payout['type'],
      'montant_ttc_client' => $payout['transaction_amount'],
      'montant_ht_client' => calculateAmountTaxFree(
        $payout['transaction_amount'], $payout['transaction_tax']
      ),
      'reversement_ht' => $payout['amount'],
      'device' => isset($user['data']['register_device']) ? $user['data']['register_device'] : null,
      'duree_abonnement' => !is_null($plan) ? $plan['duration'] / 2592000 : null,
      'nom_site' => $website['domain_name']
    ];

    var_dump($record);
  }
} catch(\Exception $e) {
  var_dump($e);
  echo 'ERROR';
}