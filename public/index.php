<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// FORCE VITE MANIFEST PATH FOR PRODUCTION
// Vite::useBuildDirectory('build') is not available before the app is booted.
// The standard Laravel way is to set it in the AppServiceProvider.
// We removed the failing Facade call from here.

$app->handleRequest(Request::capture());
