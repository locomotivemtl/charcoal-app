<?php

namespace Charcoal\App;

use \InvalidArgumentException;

// slim/slim dependencies
use \Slim\App as SlimApp;

// Local namespace dependencies
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;

/**
 *
 */
abstract class AbstractManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $config = [];

    /**
     * A SlimApp instance is required to know what App the manager is linked to.
     *
     * @var SlimApp
     */
    private $app;

    /**
     * @param array $data The dependencies container.
     */
    public function __construct(array $data)
    {
        $this->set_config($data['config']);
        $this->set_app($data['app']);

        if (isset($data['logger'])) {
            $logger = $data['logger'];
        } elseif (isset($this->app()->logger)) {
            $logger = $this->app()->logger;
        }

        if (isset($logger)) {
            $this->set_logger($logger);
        }
    }

    /**
     * Set the manager's config
     *
     * @param  array $config The manager configuration.
     * @return self
     */
    protected function set_config(array $config = [])
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get the manager's config
     *
     * @return array
     */
    protected function config()
    {
        return $this->config;
    }

    /**
     * Set the manager's reference to the Slim App.
     *
     * @param  SlimApp $app The Slim Application instance.
     * @return self
     */
    protected function set_app(SlimApp $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the manager's reference to the Slim App.
     *
     * @return SlimApp
     */
    protected function app()
    {
        return $this->app;
    }
}
