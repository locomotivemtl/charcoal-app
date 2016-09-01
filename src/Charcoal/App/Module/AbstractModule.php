<?php

namespace Charcoal\App\Module;

use \InvalidArgumentException;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependencies from PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Dependency from 'charcoal-config'
use \Charcoal\Config\ConfigurableInterface;

// Intra-module ('charcoal-app') dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;

/**
 *
 */
abstract class AbstractModule implements
    AppAwareInterface,
    ConfigurableInterface,
    LoggerAwareInterface,
    ModuleInterface
{
    use AppAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var ConfigInterface $config
     */
    protected $config;

    /**
     * @var RouteManager
     */
    protected $routeManager;

    /**
     * Return a new AbstractModule object.
     *
     * @param array $data Module dependencies.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);

        if (!isset($data['app'])) {
            $data['app'] = App::instance();
        }
        $this->setApp($data['app']);
    }

    /**
     * Set the module's config
     *
     * @param  ConfigInterface|array $config The module configuration.
     * @return AbstractModule
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Retrieve the module's configuration container, or one of its entry.
     *
     * If a key is provided, return the configuration key value instead of the full object.
     *
     * @param  string $key Optional. If provided, the config key value will be returned, instead of the full object.
     * @throws InvalidArgumentException If a config has not been defined.
     * @return \Charcoal\Config\ConfigInterface
     */
    public function config($key = null)
    {
        if ($this->config === null) {
            throw new InvalidArgumentException(
                'Configuration not set.'
            );
        }

        if ($key !== null) {
            return $this->config->get($key);
        } else {
            return $this->config;
        }
    }

    /**
     * Setup the module's dependencies.
     *
     * @return AbstractModule
     */
    public function setup()
    {
        $this->setupRoutes();

        return $this;
    }

    /**
     * Set up the module's routes, via a RouteManager
     *
     * @return AbstractModule Chainable
     */
    public function setupRoutes()
    {
        if (!isset($this->routeManager)) {
            $config = $this->config();
            $routes = (isset($config['routes']) ? $config['routes'] : [] );

            $this->routeManager = new RouteManager([
                'config' => $routes,
                'app'    => $this->app(),
                'logger' => $this->logger
            ]);

            $this->routeManager->setupRoutes();
        }

        return $this;
    }
}
