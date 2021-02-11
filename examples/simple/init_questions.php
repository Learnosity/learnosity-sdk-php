<?php

// Setup to load the necessary classes from the example directory
require_once(__DIR__ . '/../../bootstrap.php');

use LearnositySdk\Request\Init;

$security_packet = [
    'consumer_key'   => 'yis0TYCu7U9V4o7M',
    'domain'         => 'localhost',
    'timestamp'     => '20170727-2107',
    'user_id'       => 'demo-user',
];

print_r($security_packet);

# XXX: The consumer secret should be in a properly secured credential store, and *NEVER* checked in in revision control
$consumer_secret = '74c5fd430cf1242a527f6223aebd42d30464be22';

$init = new Init(
    'questions',
    $security_packet,
    $consumer_secret
);

print_r(json_decode($init->generate()));
