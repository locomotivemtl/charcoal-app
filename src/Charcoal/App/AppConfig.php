<?php

namespace Charcoal\App;

use \Charcoal\Config\AbstractConfig;

class AppConfig extends AbstractConfig
{
    /**
    * @var array $routes
    */
    protected $routes = [];

    /**
    * @var array $modules
    */
    protected $modules = [];

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
