<?php

namespace Charcoal\App;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\App\AppInterface;
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
     * A AppInterface instance is required to know what App the manager is linked to.
     *
     * @var AppInterface
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
     * @param  ConfigInterface|array $config The manager configuration.
     * @return self
     */
    public function set_config($config = [])
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get the manager's config
     *
     * @return array
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Set the manager's reference to the Slim App.
     *
     * @param  AppInterface $app The Charcoal Application instance.
     * @return self
     */
    public function set_app(AppInterface $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the manager's reference to the Slim App.
     *
     * @return AppInterface
     */
    public function app()
    {
        return $this->app;
    }
}
