<?php
/**
* An example of a default Front-Controller for Charcoal.
*
* The basic "charcoal-app" dependencies are defined in the custom Charcoal App Container.
*
* @see \Charcoal\App\AppContainer
*/

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

// This project requires composer.
include '../vendor/autoload.php';

// Main charcoal app configuration object.
$config = new AppConfig();
$config->add_file(__DIR__.'/../config/config.php');
$config->set('ROOT', dirname(__DIR__) . '/');

// Create container and configure it (with charcoal-config)
$container = new AppContainer([
    'settings'  => [],
    'config'    => $config
]);

// Charcoal / Slim is the main app
$app = App::instance($container);

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
