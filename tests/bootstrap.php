<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

//$GLOBALS['logger'] = new \Monolog\Logger('charcoal.test');
//$GLOBALS['logger']->pushHandler();

// Create container and configure it (with charcoal-config)

$config = new AppConfig();
$config->addFile(__DIR__.'/../config/config.php');

$GLOBALS['container'] = new AppContainer([
    'config'=>$config
]);

// Charcoal / Slim is the main app
$GLOBALS['app'] = new App($GLOBALS['container']);

$GLOBALS['app']->setLogger(new \Monolog\Logger('charcoal.test'));
