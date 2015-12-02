<?php

namespace Charcoal\App\Module;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;


// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;
use \Charcoal\App\Middleware\MiddlewareManager;
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;

/**
 *
 */
abstract class AbstractModule implements
    ConfigurableInterface,
    LoggerAwareInterface,
    ModuleInterface
    
{
    use ConfigurableTrait;
    use LoggerAwareTrait;

    /**
     * @var AppInterface
     */
    private $app;

    /**
     * @param array $data Module dependencies.
     */
    public function __construct(array $data)
    {
        $this->set_app($data['app']);
        $this->set_logger($data['logger']);
    }

    /**
     * @param AppInterface $app The module parent app.
     * @return ModuleInterface Chainable
     */
    public function set_app(AppInterface $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return AppInterface
     */
    public function app()
    {
        return $this->app;
    }
    
    /**
     * @return void
     */
    public function setup()
    {
        $this->setup_middlewares();
        $this->setup_routes();
    }

    /**
     * @return void
     */
    protected function setup_middlewares()
    {
        $middlewares = $this->config['middlewares'];
        if ($middlewares === null || count($middlewares === 0)) {
            return;
        }
        $middleware_manager = new MiddlewareManager([
            'logger' => $this->logger(),
            'config' => $middlewares,
            'app' => $this->app()
        ]);
        return $middleware_manager->setup_middlewares();
    }


    /**
     * Set up the app's "global" routes, via a RouteManager
     *
     * @return void
     */
    public function setup_routes()
    {
        $config = $this->config();
        $routes = $config['routes'];
        if ($routes === null || count($routes) === 0) {
            return;
        }
        $route_manager = new RouteManager([
            'logger' => $this->logger(),
            'config' => $routes,
            'app' => $this->app()
        ]);
        return $route_manager->setup_routes();
    }

    /**
     * @param array $data Optiona configuration data.
     * @return ConfigInterface
     */
    abstract public function create_config(array $data = null);
}
