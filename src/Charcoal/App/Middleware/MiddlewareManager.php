<?php

namespace Charcoal\App\Middleware;

use \InvalidArgumentException;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;


/**
*
*/
class MiddlewareManager implements LoggerAwareInterface
{
	   /**
    * @var array $config
    */
    private $config = [];

    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
    * PSR-3 Logger
    * @var LoggerInterface $logger
    */
    private $logger;

	/**
    * @param array $data The dependencies container
    * @throws InvalidArgumentException
    */
    public function __construct($data)
    {
        $this->config = $data['config'];
        $this->app    = $data['app'];

        if (!($this->app instanceof \Slim\App)) {
            throw new InvalidArgumentException(
                'RouteManager requires a Slim App object in its dependency container.'
            );
        }

        $logger = ( isset($data['logger']) ? $data['logger'] : $this->app->logger );
        $this->set_logger($data['logger']);
    }

    /**
    * > LoggerAwareInterface > setLogger()
    *
    * Fulfills the PSR-1 / PSR-3 style LoggerAwareInterface
    *
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function setLogger(LoggerInterface $logger)
    {
        return $this->set_logger($logger);
    }

    /**
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function set_logger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
    * @erturn LoggerInterface
    */
    public function logger()
    {
        return $this->logger;
    }

    public function setup_middlewares()
    {
    	// ...
    }
}