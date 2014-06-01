<?php

/*
|--------------------------------------------------------------------------
| Data format for the Learnosity Items API
|--------------------------------------------------------------------------
|
| Use this file to create the necessary data used to instantiate the
| Learnosity Items API service in assess mode, you'll need:
|   - service  (string) (mandatory)
|   - security (array)  (mandatory)
|   - secret   (string) (mandatory)
|   - request  (array)  (optional)
|
| Use the example code below as a template or as a guide. The end result
| should be a JSON object to be passed into LearnosityItems.init()
|
*/

$service = 'items';
$security = array(
    'consumer_key' => 'yis0TYCu7U9V4o7M',
    'domain'       => 'demos.vg.learnosity.com'
);
$secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
$request = array(
    'activity_id'    => 'itemsassessdemo',
    'name'           => 'Items API demo - assess activity',
    'rendering_type' => 'assess',
    'state'          => 'initial',
    'type'           => 'local_practice',
    'course_id'      => 'commoncore',
    'session_id'     => 'c6e84da0-4c04-11e3-8f96-0800200c9a66',
    'user_id'        => '12345678',
    'items'          => array('Demo3', 'Demo4', 'Demo5', 'Demo6', 'Demo7', 'Demo8', 'Demo9', 'Demo10'),
    'config'         => array(
        'subtitle'   => 'Walter White',
        'navigation' => array(
            'show_intro'     => true,
            'show_itemcount' => true
        ),
        'renderSaveButton'    => true,
        'ignore_validation'   => false,
        'assessApiVersion'    => 'v2',
        'questionsApiVersion' => 'v2'
    )
);

$heading = 'Items API';
$description = '<p>Prepare your assessment configuration and items, we\'ll do the rest!</p>';
