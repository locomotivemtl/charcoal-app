<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// From `charcoal-config`
use \Charcoal\Config\AbstractConfig;

// From `charcoal-core`
use \Charcoal\Translation\LanguageAwareInterface;
use \Charcoal\Translation\LanguageAwareTrait;

/**
 * Base "Route" configuration.
 */
class RouteConfig extends AbstractConfig implements LanguageAwareInterface
{
    use LanguageAwareTrait;

    /**
     * Route identifier/name
     *
     * @var string
     */
    private $ident;

    /**
     * Route pattern
     *
     * @var string
     */
    private $route;

    /**
     * HTTP methods supported by this route
     *
     * @var string[]
     */
    private $methods = [ 'GET' ];

    /**
     * Route view controller classname
     *
     * @var string
     */
    private $controller;

    /**
     * Parent route groups
     *
     * @var string[]|RouteGroup[]
     */
    private $groups;

    /**
     * Set route identifier
     *
     * @param string $ident Route identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return RouteConfig Chainable
     */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Route identifier must be a string.'
            );
        }

        $this->ident = $ident;

        return $this;
    }

    /**
     * Get route identifier
     *
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set route pattern
     *
     * @param string $pattern Route pattern.
     * @throws InvalidArgumentException If the pattern argument is not a string.
     * @return RouteConfig Chainable
     */
    public function set_route($pattern)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException(
                'Route pattern must be a string.'
            );
        }

        $this->route = $pattern;

        return $this;
    }

    /**
     * Get route pattern
     *
     * @return string
     */
    public function route()
    {
        return $this->route;
    }

    /**
     * Set parent route groups
     *
     * @param string[]|RouteGroup[] $groups The parent route groups.
     * @return RouteConfig Chainable
     */
    public function set_groups(array $groups)
    {
        $this->groups = [];

        foreach ($groups as $group) {
            $this->add_group($group);
        }

        return $this;
    }

    /**
     * Add parent route group
     *
     * @param string|RouteGroup $group The parent route group.
     * @throws InvalidArgumentException If the group is invalid.
     * @return RouteConfig Chainable
     */
    public function add_group($group)
    {
        if (!is_string($group)) {
            throw new InvalidArgumentException(
                'Parent route group must be a string.'
            );
        }

        $this->groups[] = $method;

        return $this;
    }

    /**
     * Get parent route groups
     *
     * @return array
     */
    public function groups()
    {
        return $this->groups;
    }

    /**
     * Set route view controller classname
     *
     * @param string $controller Route controller name.
     * @throws InvalidArgumentException If the route view controller is not a string.
     * @return RouteConfig Chainable
     */
    public function set_controller($controller)
    {
        if (!is_string($controller)) {
            throw new InvalidArgumentException(
                'Route view controller must be a string.'
            );
        }

        $this->controller = $controller;

        return $this;
    }

    /**
     * Get the view controller classname
     *
     * If not set, the `self::ident()` will be used by default.
     *
     * @return string
     */
    public function controller()
    {
        if (!isset($this->controller)) {
            return $this->ident();
        }

        return $this->controller;
    }

    /**
     * Set route methods
     *
     * @param string[] $methods The route's supported HTTP methods.
     * @return RouteConfig Chainable
     */
    public function set_methods(array $methods)
    {
        $this->methods = [];

        foreach ($methods as $method) {
            $this->add_method($method);
        }

        return $this;
    }

    /**
     * Add route method
     *
     * @param string $method The route's supported HTTP method.
     * @throws InvalidArgumentException If the HTTP method is invalid.
     * @return RouteConfig Chainable
     */
    public function add_method($method)
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported HTTP method; must be a string, received %s',
                    (is_object($method) ? get_class($method) : gettype($method))
                )
            );
        }

        // According to RFC, methods are defined in uppercase (See RFC 7231)
        $method = strtoupper($method);

        $valid_http_methods = [
            'CONNECT',
            'DELETE',
            'GET',
            'HEAD',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT',
            'TRACE',
        ];

        if (!in_array($method, $valid_http_methods)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported HTTP method "%s" provided', $method)
            );
        }

        $this->methods[] = $method;

        return $this;
    }

    /**
     * Get route methods
     *
     * @return string[]
     */
    public function methods()
    {
        return $this->methods;
    }
}
