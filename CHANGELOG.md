## v0.3.2 (2014-09-05)

* Removed validation that disallowed an empty request to be passed (as a JSON string).

## v0.3.1 (2014-07-07)

* Made sure to copy user_id into the security packet for Reports API if it wasn't in the security body, but was in the request body.

## v0.3.0 (2014-06-21)

* Added requestRecursive() to the DataApi helper when communicating with the [http://docs.learnosity.com/dataapi/index.php](Data API)

## v0.2.0 (2014-06-16)

* Added DataApi.php as a helper when communicating with the [http://docs.learnosity.com/dataapi/index.php](Data API)

## v0.1.0 (2014-05-31)

* Initial release of the Learnosity SDK for PHP. See <https://github.com/Learnosity/learnosity-sdk-php> for more information.
