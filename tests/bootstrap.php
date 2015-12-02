<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Slim\Container as SlimContainer;

$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

//$GLOBALS['logger'] = new \Monolog\Logger('charcoal.test');
//$GLOBALS['logger']->pushHandler();

// Create container and configure it (with charcoal-config)
$GLOBALS['container'] = new SlimContainer();

$GLOBALS['container']['charcoal/app/config'] = function($c) {
    $config = new AppConfig();
    $config->add_file(__DIR__.'/../config/config.php');
    return $config;
};

// Charcoal / Slim is the main app
$GLOBALS['app'] = new App($GLOBALS['container']);

$GLOBALS['app']->set_logger(new \Monolog\Logger('charcoal.test'));
