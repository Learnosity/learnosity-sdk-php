<?php

/**
 *--------------------------------------------------------------------------
 * Learnosity SDK - Autoloader
 *--------------------------------------------------------------------------
 *
 * Uses the Symfony autoloader to autoload classes. You only need this
 * if you're not using Composer and/or you don't have your own autoloader.
 *
 * Usage - include this file in any location that you want to use the
 * Learnosity SDK.
 *   Eg https://github.com/Learnosity/learnosity-sdk-php/examples/index.php
 */

require_once __DIR__ . '/Vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->register();

$loader->registerNamespace('LearnositySdk', __DIR__ . '/../');
