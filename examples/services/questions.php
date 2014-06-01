<?php

/*
|--------------------------------------------------------------------------
| Data format for the Learnosity Questions API (V2)
|--------------------------------------------------------------------------
|
| Use this file to create the necessary data used to instantiate the
| Learnosity Questions API service, you'll need:
|   - service  (string) (mandatory)
|   - security (array)  (mandatory)
|   - secret   (string) (mandatory)
|   - request  (array)  (mandatory)
|
| Use the example code below as a template or as a guide. The end result
| should be a JSON object to be passed into LearnosityApp.init()
|
*/

$service = 'questions';
$security = array(
    'consumer_key' => 'yis0TYCu7U9V4o7M',
    'domain'       => 'localhost',
    'user_id'      => 'demo_student'
);
$secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
$request = array(
    'type'      => 'local_practice',
    'state'     => 'initial',
    'questions' => array(
        array(
            'response_id' => '50001',
            'type'        => 'audio'
        ),
        array(
            'response_id' => '50002',
            'type'        => 'audio'
        ),
        array(
            'response_id' => '50003',
            'type'        => 'audio'
        )
    )
);

$heading = 'Questions API';
$description = '<p>Prepare your question JSON and security credentials, we\'ll do the rest!</p>';
