<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

use \Charcoal\Charcoal;
use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

// If using PHP's built-in server, return false for existing files on filesystem
if (PHP_SAPI === 'cli-server') {
    $filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (is_file($filename)) {
        return false;
    }
}

// Require the Charcoal Framework
include '../vendor/autoload.php';

$settings = [];
$config = new AppConfig();
$config->add_file(__DIR__.'/../config/config.php');
$config->set('ROOT', dirname(__DIR__) . '/');

// Create container and configure it (with charcoal-config)
$container = new AppContainer([
    'settings'=>$settings,
    'config'=>$config
]);

// Charcoal / Slim is the main app
$app = new App($container);

// Set up dependencies
require __DIR__.'/../config/dependencies.php';
// Register middlewares
require __DIR__.'/../config/middlewares.php';

// Remove me. This is for backward compatibility.
$container['config'] = function($c) {
    $config = new \Charcoal\CharcoalConfig();
    $config->add_file(__DIR__.'/../config/config.php');
    $config->set('ROOT', dirname(__DIR__) . '/');
    return $config;
};
$container['charcoal/config'] = $container['config'];
\Charcoal\Charcoal::init([
    'app'    => $app,
    'config' => $container['charcoal/config'],
    'logger' => $container['logger']
]);

if (!session_id()) {
    session_start();
}

$app->run();
