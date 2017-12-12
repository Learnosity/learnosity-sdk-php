<?php

// Setup to load the necessary classes from the example directory
require_once(__DIR__ . '/../../bootstrap.php');

use LearnositySdk\Request\DataApi;
use LearnositySdk\Request\Remote;

$security_packet = [
    'consumer_key'   => 'yis0TYCu7U9V4o7M',
    'domain'         => 'localhost',
];

# XXX: The consumer secret should be in a properly secured credential store, and *NEVER* checked in in revision control
$consumer_secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
$data_request = [ 'items' => [ 'dataapiMCQ10' ] ];

$DataApi = new DataApi();

$sessionTemplatesUri = 'https://data.learnosity.com/v1/sessions/templates';
print(">>> [{$sessionTemplatesUri}] " . json_encode($data_request) . PHP_EOL);

$res = $DataApi->request(
    $sessionTemplatesUri,
    $security_packet,
    $consumer_secret,
    $data_request,
    'get'
);

print("<<< [{$res->getStatusCode()}] {$res->getBody()}" . PHP_EOL);
