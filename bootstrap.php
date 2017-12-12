<?php

define('BASE_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'src');

spl_autoload_register(
    function ($class) {
        $filename = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        require_once($filename);
    }
);
