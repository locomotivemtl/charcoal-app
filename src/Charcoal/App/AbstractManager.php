<?php

namespace Charcoal\App;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\App\App as CharcoalApp;
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
     * A CharcoalApp instance is required to know what App the manager is linked to.
     *
     * @var CharcoalApp
     */
    private $app;

    /**
     * @param array $data The dependencies container.
     */
    public function __construct(array $data)
    {
        $this->set_config($data['config']);
        $this->set_app($data['app']);
        $this->set_logger($data['logger']);
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
     * @param  CharcoalApp $app The Charcoal Application instance.
     * @return self
     */
    protected function set_app(CharcoalApp $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the manager's reference to the Slim App.
     *
     * @return CharcoalApp
     */
    protected function app()
    {
        return $this->app;
    }
}
