<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

use \Charcoal\App\App as CharcoalApp;
use \Charcoal\App\AppConfig;
use \Slim\App as SlimApp;
use \Slim\Container as SlimContainer;


// If using PHP's built-in server, return false for existing files on filesystem
if (php_sapi_name() === 'cli-server') {
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (is_file($filename)) {
        return false;
    }
}

/** Require the Charcoal Framework */
include '../vendor/autoload.php';

// Create container and configure it (with charcoal-config)
$container = new SlimContainer();

$container['config'] = function($c) {
    $config = new AppConfig();
    $config->add_file(__DIR__.'/../config/config.php');
    return $config;
};

// Slim is the main app
$app = new SlimApp($container);

// Set up dependencies
require __DIR__.'/../config/dependencies.php';
// Register middleware
require __DIR__.'/../config/middlewares.php';
// Register routes
require __DIR__.'/../config/routes.php';

$charcoal = new CharcoalApp($container['config'], $app);
$charcoal->setup();


$app->run();

