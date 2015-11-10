<?php

namespace Charcoal\App;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;

use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;

/**
* ## Dependencies
* - **config** (`\Charcoal\App\AppConfig`)
* - **app** (`\Slim\App`)
*/
class App implements
    AppInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
    * @var \Psr\Log\LoggerInterface
    */
    private $logger;

    /**
    * # Required dependencies
    * - `logger` A PSR-3 logger.
    *
    * @param \Slim\App $app The Slim object to attach events to
    * @param \Charcoal\App\AppConfig $config
    */
    public function __construct($config, $app)
    {
        $this->set_config($config);
        $this->set_app($app);

        $container = $app->getContainer();
        $this->logger = $container['logger'];
        $this->logger->debug('Init logger');
    }

    public function set_app(\Slim\App $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
    * @return void
    */
    public function setup()
    {
        $this->setup_middlewares();
        $this->setup_routes();
        $this->setup_modules();
    }

    /**
    * @return void
    */
    protected function setup_middlewares()
    {
        $config = $this->config();
        $middlewares = $config['middlewares'];
        if ($middlewares === null || count($middlewares === 0)) {
            return;
        }
        $middleware_manager = new MiddlewareManager([
            'config' => $middlewares,
            'app' => $this->app,
            'logger' => $this->logger
        ]);
        return $middleware_manager->setup_middlewares();
    }

    /**
    * Set up the app's "global" routes, via a RouteManager
    *
    * @return void
    */
    protected function setup_routes()
    {
        $config = $this->config();
        $routes = $config['routes'];
        if ($routes === null || count($routes) === 0) {
            return;
        }
        $route_manager = new RouteManager([
            'config' => $routes,
            'app' => $this->app,
            'logger' => $this->logger
        ]);
        return $route_manager->setup_routes();
    }

    /**
    * @return void
    */
    protected function setup_modules()
    {
        $config = $this->config();
        $modules = $config['modules'];
        if ($modules === null || count($modules) === 0) {
            return;
        }
        
        $module_manager = new ModuleManager([
            'modules' => $modules,
            'app' => $this->app,
            'logger' => $this->logger
        ]);
        return $module_manager->setup_modules();
    }

    /**
    * ConfigurableTrait > create_config()
    *
    * @param array $data
    * @return AppConfig
    */
    public function create_config($data = null)
    {
        return new AppConfig($data);
    }
}
