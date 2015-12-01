<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// From `charcoal-config`
use \Charcoal\Config\AbstractConfig;

/**
 * Base "Route" configuration.
 */
class RouteConfig extends AbstractConfig
{
    /**
     * @var string $ident
     */
    private $ident;

    /**
     * The HTTP methods to wthich this route resolve to.
     * Ex: ['GET', 'POST', 'PUT', 'DELETE']
     * @var array $methods
     */
    private $methods = ['GET'];

    /**
     * The identifier of the controller class.
     * @var string $controller
     */
    private $controller;

    /**
     * @var string $lang
     */
    private $lang;

    /**
     * @var string $group
     */
    private $group;

    /**
     * @param string $ident Route identifier.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @return AbstractRouteConfig Chainable
     */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Route ident must be a string'
            );
        }
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
     * @param string $controller Route controller name.
     * @throws InvalidArgumentException If the controller argument is not a string.
     * @return AbstractRouteConfig Chainable
     */
    public function set_controller($controller)
    {
        if (!is_string($controller)) {
            throw new InvalidArgumentException(
                'Route controller must be a string'
            );
        }
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return string
     */
    public function controller()
    {
        if ($this->controller === null) {
            return $this->ident();
        }
        return $this->controller;
    }

    /**
     * @param string $lang The language code for current route.
     * @throws InvalidArgumentException If the lang parameter is not a string.
     * @return ActionInterface Chainable
     */
    public function set_lang($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Can not set language, lang parameter needs to be a string.'
            );
        }
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return string
     */
    public function lang()
    {
        return $this->lang;
    }

    /**
     * @param array $methods The available methods for this route. (ex: ['GET']).
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
