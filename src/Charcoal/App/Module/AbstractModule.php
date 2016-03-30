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
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;
use \Charcoal\App\Routable\RoutableFactory;

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
    private $config;

    /**
     * @var RouteManager
     */
    private $routeManager;

    /**
     * Return a new AbstractModule object.
     *
     * @param array $data Module dependencies.
     */
    public function __construct(array $data)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
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
     * @return AbstractModule
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

        return $this->setupRoutables();
    }

    /**
     * Setup the module's "global" routables.
     *
     * @return ModuleInterface Chainable
     */
    protected function setupRoutables()
    {
        $config = $this->config();

        if (isset($config['base_path'])) {
            $container = $this->app()->getContainer();

            // For now, need to rely on a catch-all...
            $this->app()->get(
                '{catchall:.*}',
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    array $args
                ) use (
                    $config,
                    $container
                ) {
                    $routables = $config['routables'];
                    if ($routables === null || count($routables) === 0) {
                        return $container['notFoundHandler']($request, $response);
                    }
                    $routableFactory = new RoutableFactory();
                    foreach ($routables as $routableType => $routableOptions) {
                        $routable = $routableFactory->create($routableType);
                        $route = $routable->routeHandler($args['catchall'], $request, $response);
                        if ($route) {
                            return $route($request, $response);
                        }
                    }

                    // If this point is reached, no routable has provided a callback. 404.
                    return $container['notFoundHandler']($request, $response);
                }
            );
        }

        return $this;
    }
}
