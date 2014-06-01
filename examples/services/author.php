<?php

/*
|--------------------------------------------------------------------------
| Data format for the Learnosity Author API
|--------------------------------------------------------------------------
|
| Use this file to create the necessary data used to instantiate the
| Learnosity Author API, you'll need:
|   - service  (string) (mandatory)
|   - security (array)  (mandatory)
|   - secret   (string) (mandatory)
|   - request  (array)  (optional)
|
| Use the example code below as a template or as a guide. The end result
| should be a JSON object to be passed into LearnosityAuthor.init()
|
*/

$service = 'author';
$security = array(
    'consumer_key' => 'yis0TYCu7U9V4o7M',
    'domain'       => 'localhost',
    'timestamp'    => gmdate('Ymd-Hi')
);
$secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
$request = array(
    'limit' => 100,
    'tags' => array(
      array('type' => 'course', 'name' => 'commoncore')
    )
);

$heading = 'Author API';
$description = '<p>Retrieve content from the Learnosity ItemBank to embed in your own authoring environment.</p>';
