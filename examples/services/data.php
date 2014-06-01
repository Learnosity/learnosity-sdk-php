<?php

/*
|--------------------------------------------------------------------------
| Data format for the Learnosity Data API
|--------------------------------------------------------------------------
|
| Use this file to create the necessary data used to instantiate the
| Learnosity Data API service, you'll need:
|   - service  (string) (mandatory)
|   - security (array)  (mandatory)
|   - secret   (string) (mandatory)
|   - request  (array)  (optional)
|   - action   (string) (optional)
|
| Use the example code below as a template or as a guide. The end result
| should be a series of key|value pairs to be passed POSTed to the
| Learnosity Data API
|
*/

$service = 'data';
$security = array(
    'consumer_key' => 'yis0TYCu7U9V4o7M',
    'domain'       => 'localhost'
);
$secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
$request = array(
    'limit' => 100
);
$action = 'get';

$heading = 'Data API';
$description = '<p>Use the Data API to retrieve or update content in the Learnosity Assessment Platform.</p>';
