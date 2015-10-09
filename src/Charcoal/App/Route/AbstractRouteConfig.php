<?php

namespace Charcoal\App\Route;

// From `charcoal-config`
use \Charcoal\Config\AbstractConfig;

class AbstractRouteConfig extends AbstractConfig
{

    /**
    * The route ident
    */
    private $ident;

    /**
    * The methods to wthich this route resolve to.
    * Ex: ['GET', 'POST', 'PUT', 'DELETE']
    * @var array $methods
    */
    private $methods;

    /**
    * The identifier of the controller class.
    * @var string $controller
    */
    private $controller;


    /**
    * @param string $ident
    * @return AbstractRouteConfig Chainable
    */
    public function set_ident($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * @param string $controller
    * @return AbstractRouteConfig Chainable
    */
    public function set_controller($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
    * @return string
    */
    public function controller()
    {
        if ($this->controller === null) {
            return $this->default_controller();
        }
        return $this->controller;
    }

    /**
    * @return string
    */
    public function default_controller()
    {
        return $this->ident();
    }

    /**
    * @param array $methods
    * @return AbstractRouteConfig Chainable
    */
    public function set_methods(array $methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
    * @return array
    */
    public function methods()
    {
        return $this->methods;
    }
}
