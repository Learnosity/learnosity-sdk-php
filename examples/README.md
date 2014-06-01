# Learnosity SDK - Examples

* @version v0.1.0
* @author Michael Sharman <michael.sharman@learnosity.com>
* @link https://github.com/Learnosity/learnosity-sdk-php
*
* This file will accept a GET parameter called `service` that will
* include a file from the `examples/services` directory.
* This will represent the service you are trying to integrate with.
*
* Open a file (eg examples/services/items.php), set your security
* and request values. Or you can simply review the file to use as a guide.
*
* These values are passed to the Request.php class and will generate the
* necessary request packet when calling the generate() method.
*
* The easiest way to test this (assuming PHP 5.4+) is to use the built in
* local server.
*     cd ./examples
*     php -S localhost:5000
*
* Then visit http://localhost:5000 in a browser.
*
* If using an earlier version of PHP, add the contents of learnosity-sdk-php
* to your webroot and visit http://localhost/examples/index.php
*/
