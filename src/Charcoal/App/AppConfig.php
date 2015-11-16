<?php

namespace Charcoal\App;

use \Charcoal\Config\AbstractConfig;

/**
* Charcoal App configuration
*/
class AppConfig extends AbstractConfig
{
    /**
    * @var array $routes
    */
    private $routes = [];

    /**
    * @var array $routables
    */
    private $routables = [];

    /**
    * @var array $modules
    */
    private $modules = [];

    /**
    * @param array $routes
    * @return AppConfig Chainable
    */
    public function set_routes(array $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
    * @return array
    */
    public function routes()
    {
        return $this->routes;
    }

    /**
    * @param array $routes
    * @return AppConfig Chainable
    */
    public function set_routables(array $routables)
    {
        $this->routables = $routables;
        return $this;
    }

    /**
    * @return array
    */
    public function routables()
    {
        return $this->routables;
    }

    /**
    * @param array $modules
    * @return AppConfig Chainable
    */
    public function set_modules(array $modules)
    {
        $this->modules = $modules;
        return $this;
    }

    /**
    * @return array
    */
    public function modules()
    {
        return $this->modules;
    }
}
