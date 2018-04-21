<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Authorization');

header('Content-Type: application/json');

ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Moscow');

// Imports
use Core\App;

// Get composer included vendors.
require __DIR__ . '/../vendor/autoload.php';

// Start app
$app = new App();
$app->configure();
$app->run();
