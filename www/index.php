<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

use \Charcoal\Charcoal;
use \Charcoal\App\App as CharcoalApp;
use \Charcoal\App\AppConfig;
use \Slim\App as SlimApp;
use \Slim\Container as SlimContainer;

// If using PHP's built-in server, return false for existing files on filesystem
if (PHP_SAPI === 'cli-server') {
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (is_file($filename)) {
        return false;
    }
}

// Require the Charcoal Framework
include '../vendor/autoload.php';

Charcoal::init([
    'config'=>new \Charcoal\CharcoalConfig('../config/config.php')
]);

// Create container and configure it (with charcoal-config)
$container = new SlimContainer();

$container['charcoal/app/config'] = function($c) {
    $config = new AppConfig();
    $config->add_file(__DIR__.'/../config/config.php');
    return $config;
};

// Handle "404 Not Found"
$container['notFoundHandler'] = function ($c) 
{ 
    return function ($request, $response) use ($c) 
    { 
        return $c['response'] 
            ->withStatus(404) 
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found'."\n"); 
    }; 
};

// Handle "500 Server Error" 
$container['errorHandler'] = function ($c) 
{
    return function ($request, $response, $exception) use ($c) 
    {
        return $c['response']
            ->withStatus(500)
			->withHeader('Content-Type', 'text/html')
			->write(
                sprintf('Something went wrong! (%s)'."\n", $exception->getMessage())
            );
	};
};

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
