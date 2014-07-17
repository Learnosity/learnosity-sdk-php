<?php

/*
|--------------------------------------------------------------------------
| Response format for the Learnosity Schemas API
|--------------------------------------------------------------------------
|
| This file makes a request to the public Schemas API. This API does
| not require authentication, simply a GET request to retrieve JSON
| schemas for use with the Question Editor API.
|
| Use the example code below as a template or as a guide. The end result
| should be a JSON object.
|
*/

use LearnositySdk\Request\Remote;

$Remote = new Remote();
$response = $Remote->get('http://schemas.learnosity.com/latest/questions/templates');

$requestPacket = $response->getBody();

$service = 'schemas';
$heading = 'Schemas API';
$description = '<p>Retrieve JSON schema information for question types, attributes and templates.</p>';
