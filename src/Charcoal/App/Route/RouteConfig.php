<?php

namespace Charcoal\App\Route;

use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Base "Route" configuration.
 */
class RouteConfig extends AbstractConfig
{
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
     * Response controller classname
     *
     * Should be the class-ident of an action, a script or a template controller.
     *
     * @var string
     */
    private $controller;

    /**
     * Parent route groups
     *
     * @var string[]
     */
    private $groups = [];

    /**
     * Optional headers to set on response.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Retrieve the default route types.
     *
     * @return array
     */
    public static function defaultRouteTypes()
    {
        return [
            'templates',
            'actions',
            'scripts'
        ];
    }

    /**
     * Set route identifier
     *
     * @param string $ident Route identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return self
     */
    public function setIdent($ident)
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
     * Set route pattern.
     *
     * @param string $pattern Route pattern.
     * @throws InvalidArgumentException If the pattern argument is not a string.
     * @return self
     */
    public function setRoute($pattern)
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
     * @param string[] $groups The parent route groups.
     * @return self
     */
    public function setGroups(array $groups)
    {
        $this->groups = [];

        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * Add parent route group
     *
     * @param string $group The parent route group.
     * @throws InvalidArgumentException If the group is invalid.
     * @return self
     */
    public function addGroup($group)
    {
        if (!is_string($group)) {
            throw new InvalidArgumentException(
                'Parent route group must be a string.'
            );
        }

        $this->groups[] = $group;

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
     * Add custom headers
     *
     * @param array $headers The custom headers, in key=>val pairs.
     * @return self
     */
    public function setHeaders(array $headers)
    {
        $this->headers = [];
        foreach ($headers as $name => $val) {
            $this->addHeader($name, $val);
        }
        return $this;
    }

    /**
     * @param string $name The header name (ex: "Content-Type", "Cache-Control").
     * @param string $val  The header value.
     * @throws InvalidArgumentException If the header name or value is not a string.
     * @return $this
     */
    public function addHeader($name, $val)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                'Route header name must be a string.'
            );
        }
        if (!is_string($val)) {
            throw new InvalidArgumentException(
                'Route header value must be a string.'
            );
        }
        $this->headers[$name] = $val;
        return $this;
    }

    /**
     * Get custom route headers.
     *
     * @return array
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Set route view controller classname
     *
     * @param string $controller Route controller name.
     * @throws InvalidArgumentException If the route view controller is not a string.
     * @return self
     */
    public function setController($controller)
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
     * @return self
     */
    public function setMethods(array $methods)
    {
        $this->methods = [];

        foreach ($methods as $method) {
            $this->addMethod($method);
        }

        return $this;
    }

    /**
     * Add route HTTP method.
     *
     * @param string $method The route's supported HTTP method.
     * @throws InvalidArgumentException If the HTTP method is invalid.
     * @return self
     */
    public function addMethod($method)
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

        $validHttpMethods = [
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

        if (!in_array($method, $validHttpMethods)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be one of "%s", received "%s"',
                implode('","', $validHttpMethods),
                $method
            ));
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
