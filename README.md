# Learnosity SDK - PHP

Include this package into your own codebase to ease integration with any of the Learnosity APIs.

## Version
@version v0.1.0 - June 2014

## Installation

```
git clone git@github.com:Learnosity/learnosity-sdk-php.git
```

You can play with the `examples` folder, but you'll likely just use the contents of `src` in your own project. Eg:

```
/yourProjectRoot
  /src
  /vendor
    /LearnositySdk
```

This packages follows the PSR code conventions which includes namespaces and import statements. Add the LearnositySdk autoloader or use your own (use *LearnositySdk* as the namespace):

```
require_once __DIR__ . 'vendor/LearnositySdk/autoload.php';
```


## Usage - Init
```
// Instantiate the SDK Init class with your security and request data:
$Init = new Init(
   'questions',
   array(
       'consumer_key' => 'yis0TYCu7U9V4o7M',
       'domain'       => 'localhost',
       'user_id'      => 'demo_student'
   ),
   'superfragilisticexpialidocious',
   array(
       'type'      => 'local_practice',
       'state'     => 'initial',
       'questions' => array(
           array(
               "response_id"        => "60005",
               "type"               => "association",
               "stimulus"           => "Match the cities to the parent nation.",
               "stimulus_list"      => array("London", "Dublin", "Paris", "Sydney"),
               "possible_responses" => array("Australia", "France", "Ireland", "England"),
               "validation" => array(
                   "valid_responses" => array(
                       array("England"), array("Ireland"), array("France"), array("Australia")
                   )
               ),
               "instant_feedback" => true
           )
       )
   )
);

// Call the generate() method to retrieve a JavaScript object
$request = $Init->generate();

// Pass the object to the initialisation of any Learnosity API
LearnosityApp.init($request);
```

Note that the SDK accepts JSON and native PHP arrays.

## Usage - Remote
```
// Instantiate the SDK Remote class:
$Remote = new Remote();
// Call get() or post() with a URL:
$response = $Remote->get('http://schemas.learnosity.com/stable/questions/templates');

// getBody() gives you to body of the request
$requestPacket = $response->getBody();
```

### Remote methods
getBody()

getError()

getHeader()

getSize()

getStatusCode()
