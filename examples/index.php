<?php

date_default_timezone_set('UTC');

require_once __DIR__ . '/../src/LearnositySdk/autoload.php';

use LearnositySdk\Request\Init;

if (isset($_GET['service'])) {
    // Request defaults - will be overridden from a sample `services` file
    $service  = null;
    $security = null;
    $secret   = null;
    $request  = null;
    $action   = null;
    if (is_readable(__DIR__ . '/services/' . $_GET['service'] . '.php')) {
        require_once __DIR__ . '/services/' . $_GET['service'] . '.php';
        if ($_GET['service'] !== 'schemas') {
            // Instantiate the Init class to generate initialisation data
            $Init = new Init($service, $security, $secret, $request, $action);
            $requestPacket = $Init->generate();
        }
    }
}

require_once 'includes/example.php';
