<?php

namespace Charcoal\App\Module;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;


// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;

// Local namespace dependencies
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;

/**
*
*/
class AbstractModule implements
    ModuleInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;
    
    // ...
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
            'config' => $middlewares,
            'app' => $this->app
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
        $routes = $this->config['routes'];
        if ($routes === null || count($routes) === 0) {
            return;
        }
        $route_manager = new RouteManager([
            'config' => $routes,
            'app' => $this->app
        ]);
        return $route_manager->setup_routes();
    }
}
