<?php
/**
* Global, custom, project-wide dependencies
*/

use \Doctrine\Common\Cache\MemcacheCache;

use \Monolog\Logger as MonologLogger;
use \Monolog\Processor\UidProcessor;
use \Monolog\Handler\StreamHandler as MonologStreamHandler;

// $app is a \Slim\App. $container is a \Slim\Container, which is a \Pimple\Pimple.
$container = $app->getContainer();

// PSR-3 logger with monolog
$container['logger'] = function ($c) {
    $logger = new MonologLogger('Charcoal');
    $logger->pushProcessor(new UidProcessor());
    $logger->pushHandler(new MonologStreamHandler('charcoal.app.log', \Monolog\Logger::DEBUG));
    return $logger;
};
