<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

use \Charcoal\Charcoal;
use \Charcoal\App\App as CharcoalApp;
use \Charcoal\App\AppConfig;
use \Slim\App as SlimApp;
use \Slim\Container as SlimContainer;

include '../vendor/locomotivemtl/charcoal-core/src/Charcoal/charcoal.php';

// If using PHP's built-in server, return false for existing files on filesystem
if (PHP_SAPI === 'cli-server') {
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (is_file($filename)) {
        return false;
    }
}

/** Require the Charcoal Framework */
include '../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

// Create container and configure it (with charcoal-config)
$container = new SlimContainer($configuration);

$container['charcoal/app/config'] = function($c) {
    $config = new AppConfig();
    $config->add_file(__DIR__.'/../config/config.php');
    return $config;
};

\Charcoal\Charcoal::init([
    'config'=>new \Charcoal\CharcoalConfig('../config/config.php')
]);


// $container['errorHandler'] = function ($c) {
//     return function ($request, $response, $exception) use ($c) {
//     	var_dump($exception);
//         return $c['response']->withStatus(500)
// 			->withHeader('Content-Type', 'text/html')
// 			->write('Something went wrong!');
// 	};
// };

// Slim is the main app
$app = new SlimApp($container);

// Set up dependencies
require __DIR__.'/../config/dependencies.php';
// Register middlewares
require __DIR__.'/../config/middlewares.php';
// Register routes
require __DIR__.'/../config/routes.php';

$charcoal = new CharcoalApp([
    'config' => $container['charcoal/app/config'], 
    'app' =>$app
]);
$charcoal->setup();



$app->run();

