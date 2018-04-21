<?php

require_once __DIR__.'/vendor/autoload.php';

use GO\Scheduler;

// Create a new scheduler
$scheduler = new Scheduler();

$scheduler->call(function () {
    echo "Hello";

    return " world!";
})->output('my_file.log');

// ... configure the scheduled jobs (see below) ...

// Let the scheduler execute jobs which are due.
$scheduler->run();
