# Learnosity SDK - PHP

[![Latest Stable Version](https://poser.pugx.org/learnosity/learnosity-sdk-php/version.svg)](https://packagist.org/packages/learnosity/learnosity-sdk-php)
[![Latest Unstable Version](https://poser.pugx.org/learnosity/learnosity-sdk-php/v/unstable.svg)](https://packagist.org/packages/learnosity/learnosity-sdk-php)
[![Build Status](https://travis-ci.org/Learnosity/learnosity-sdk-php.svg?branch=master)](https://travis-ci.org/Learnosity/learnosity-sdk-php)
[![License](https://poser.pugx.org/learnosity/learnosity-sdk-php/license.svg)](https://packagist.org/packages/learnosity/learnosity-sdk-php)
[![Downloads](https://poser.pugx.org/learnosity/learnosity-sdk-php/d/total.svg)](https://packagist.org/packages/learnosity/learnosity-sdk-php)

Include this package into your own codebase to ease integration with any of the Learnosity APIs.

This SDK should run on PHP 7 and 8.

## Installation

### Quick Start

For early test and proof of concepts, the easiest it to follow our [Quick Start Guide].

You can find the latest version of the SDK as a self-contained ZIP file in the [GitHub Releases].
The distribution ZIP file contains all the necessary dependencies.

For more production-ready deployment, you should use `composer`, as described
next.

### Composer

Using [Composer] is the recommended way to install the Learnosity SDK for PHP. The easiest way is to run this from your parent project folder:

    composer require "learnosity/learnosity-sdk-php"

You can also specify the requirement manually by adding "learnosity/learnosity-sdk-php" as a dependency in your project's composer.json file.

    {
        "require": {
            "learnosity/learnosity-sdk-php": "0.*"
        }
    }

Then, install the new dependency with

    composer update learnosity/learnosity-sdk-php


## Usage

There are three main classes:

 * Init, which creates the signed security packets to initialise the Javascript APIs;
 * Remote, which encapsulate remote calls to the APIs and their responses;
 * DataAPI, which allows to interact with the Data API from PHP code.

### Examples

A couple of basic examples are included in the `examples/` directory. You can run them with, e.g.,

    php examples/simple/init_items.php

or

    php examples/simple/init_data.php

You can find a complete PHP site with examples of Learnosity APIs integration in our [demos site](https://demos.learnosity.com/).

You can download the entire site or browse the code directly on [github](https://github.com/Learnosity/learnosity-demos/).

### Init

The Init class is used to create the necessary *security* and *request* details used to integrate with a Learnosity API. Most often this will be a JavaScript object.

``` php
//Include the Init classes.
use LearnositySdk\Request\Init;
```

The Init constructor takes up to 5 arguments:

 * [string]  service type
 * [array]   security details (**no secret**)
 * [string]  secret
 * [request] request details *(optional)*
 * [string]  action *(optional)*

``` php
<?php

// Instantiate the SDK Init class with your security and request data:
$Init = new Init(
   'questions',
   [
       'consumer_key' => 'yis0TYCu7U9V4o7M',
       'domain'       => 'localhost',
       'user_id'      => '$ANONYMIZED_USER_ID'
   ],
   'superfragilisticexpialidocious',
   [
       'type'      => 'local_practice',
       'state'     => 'initial',
       'questions' => [
           [
               'response_id'        => '60005',
               'type'               => 'association',
               'stimulus'           => 'Match the cities to the parent nation.',
               'stimulus_list'      => ['London', 'Dublin', 'Paris', 'Sydney'],
               'possible_responses' => ['Australia', 'France', 'Ireland', 'England'],
               'validation' => [
                   'valid_responses' => [
                       ['England'], ['Ireland'], ['France'], ['Australia']
                   ]
               ]
           ]
       ]
   ]
);

// Call the generate() method to retrieve a JavaScript object
$request = $Init->generate();
?>

// Pass the object to the initialisation of any Learnosity API, in this example the Questions API
<script src="//questions.learnosity.com"></script>
<script>
    var questionsApp = LearnosityApp.init(<?php echo $request; ?>);
</script>
```

#### Init() Arguments
**service**<br>
A string representing the Learnosity service (API) you want to integrate with. Valid options are:

* assess
* author
* data
* events
* items
* questions
* reports

**security**<br>
An array^ that includes your *consumer_key* but does not include your *secret*. The SDK sets defaults for you, but valid options are:

* consumer_key
* domain (optional - defaults to *localhost*)
* timestamp (optional - the SDK will generate this for you)
* user_id (optional - not necessary for all APIs)

^Note – the SDK accepts a JSON string and native PHP arrays.

**secret**<br>
Your private key, as provided by Learnosity.

**request**<br>
An optional associative array^ of data relevant to the API being used. This will be any data minus the security details that you would normally use to initialise an API.

^Note – the SDK accepts a JSON string and native PHP arrays.

**action**<br>
An optional string used only if integrating with the Data API. Valid options are:

* get
* set
* update
* delete

<hr>

### Remote

The Remote class is used to make server side, cross domain requests. Think of it as a cURL wrapper.

You'll call either `get()` or `post()` (mimicking the HTTP request type you want to make) with the following arguments:

* [string] URL
* [array]  Data payload
* [array]  Options


``` php
//Include the Remote classes.
use LearnositySdk\Request\Remote;
```

``` php
// Instantiate the SDK Remote class:
$Remote = new Remote();
// Call get() or post() with a URL:
$response = $Remote->get('http://schemas.learnosity.com/latest/questions/templates');

// getBody() gives you to body of the request
$requestPacket = $response->getBody();
```

#### Remote() arguments

**URL**<br>
A string URL, including schema and path. Eg:

    https://schemas.learnosity.com/latest/questions/templates

**Data**<br>
An optional array of data to be sent as a payload. For GET it will be a URL encoded query string.

**Options**<br>
An optional array of [cURL parameters](http://www.php.net/manual/en/curl.constants.php).

#### Remote methods
The following methods are available after making a `get()` or `post()`.

**getBody()**<br>
Returns the body of the response payload.

**getError()**<br>
Returns an array that includes the error code and message (if an error was thrown)

**getHeader()**<br>
Currently only returns the *content_type* header of the response.

**getSize()**<br>
Returns the size of the response payload in bytes.

**getStatusCode()**<br>
Returns the HTTP status code of the response.

<hr>

### DataApi

This is a helper class for use with the Data API. It creates the initialisation packet and sends a request to the Data API, returning an instance of Remote. You can then interact as you would with Remote, e.g., `getBody()`

#### request()

Used for a single request to the Data API. You can call as many times as necessary. It will return a `Remote` object, on which `getBody()` needs to be called to get the contents of the response.

``` php
$DataApi = new DataApi();
$response = $DataApi->request(
    'https://data.learnosity.com/v1/itembank/items',
    [
       'consumer_key' => 'yis0TYCu7U9V4o7M',
       'domain'       => 'localhost'
    ],
    'superfragilisticexpialidocious',
    [
       'limit' => 20
    ],
    'get' // optional, will default to 'get' in the backend if unspecified
);
```

#### requestRecursive()

Used to make recursive requests to the Data API, using the *next* token, for as much data is returned based on the request filtering provided.

You can pass a callback as the 6th argument, that will be executed upon completion of every request.

``` php
$DataApi = new DataApi();

$response = $DataApi->requestRecursive(
    'https://data.learnosity.com/v1/itembank/items',
    [
       'consumer_key' => 'yis0TYCu7U9V4o7M',
       'domain'       => 'localhost'
    ],
    'superfragilisticexpialidocious',
    [
       'limit' => 20
    ],
    'get',
    function ($data) {
        $this->processData($data);
    }
);

function processData($data)
{
    // Do something with $data
}
```

#### Overriding the remote options

You can send an array to the DataAPI constructor to override any remote (cURL) options, e.g.:

``` php
$options = array(
    'connect_timeout' => 20
    'timeout' => 60
);

$dataapi = new DataApi($options);
```


## Autoload

If you're not using Composer and/or you don't have your own autoloader, you can use the bootstrap provided by this package. It follows the PSR code convention which includes namespaces and import statements.

``` php
require_once __DIR__ . '/PATH/TO/learnosity_sdk/bootstrap.php';
```

[Quick Start Guide]: https://docs.learnosity.com/assessment/items/quickstart
[GitHub Releases]: https://github.com/Learnosity/learnosity-sdk-php/releases
[Composer]: https://getcomposer.org/


## Tracking
In version v0.10.0 we introduced code to track the following information by adding it to the request being signed:
- SDK version
- SDK language
- SDK language version
- Host platform (OS)
- Platform version

We use this data to enable better support and feature planning. All subsequent versions of the SDK shall include this usage tracking.
