<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;

/**
 *
 */
class RouteManager extends AbstractManager
{
    /**
     * Set up the routes.
     *
     * There are 3 types of routes:
     *
     * - Templates
     * - Actions
     * - Scripts
     *
     * @return void
     */
    public function setupRoutes()
    {
        $routes = $this->config();

        if (PHP_SAPI == 'cli') {
            $scripts = ( isset($routes['scripts']) ? $routes['scripts'] : [] );
            foreach ($scripts as $scriptIdent => $scriptConfig) {
                $this->setupScript($scriptIdent, $scriptConfig);
            }
        } else {
            $templates = ( isset($routes['templates']) ? $routes['templates'] : [] );
            foreach ($templates as $routeIdent => $templateConfig) {
                $this->setupTemplate($routeIdent, $templateConfig);
            }

            $actions = ( isset($routes['actions']) ? $routes['actions'] : [] );
            foreach ($actions as $actionIdent => $actionConfig) {
                $this->setupAction($actionIdent, $actionConfig);
            }
        }
    }

    /**
     * Add template route.
     *
     * Typically for a GET request, the route will render a template.
     *
     * @param  string             $routeIdent     The template's route identifier.
     * @param  array|\ArrayAccess $templateConfig The template's config for the route.
     * @throws InvalidArgumentException If the route identifier is not a string.
     * @return void
     */
    public function setupTemplate($routeIdent, $templateConfig)
    {
        if (!is_string($routeIdent)) {
            throw new InvalidArgumentException(
                'Can not setup route template, route identifier is not a string'
            );
        }

        $templateIdent = isset($templateConfig['ident'])
            ? $templateConfig['ident']
            : $routeIdent;

        $templateIdent = ltrim($templateIdent, '/');
        $templateConfig['ident'] = $templateIdent;

        $routePattern = isset($templateConfig['route'])
            ? $templateConfig['route']
            : $routeIdent;

        $routePattern = '/'.ltrim($routePattern, '/');
        $templateConfig['route'] = $routePattern;

        $methods = isset($templateConfig['methods'])
            ? $templateConfig['methods']
            : [ 'GET' ];

        $routeHandler = $this->app()->map(
            $methods,
            $routePattern,
            function (
                RequestInterface $request,
                ResponseInterface $response,
                array $args = []
            ) use (
                $templateIdent,
                $templateConfig
            ) {
                $this['logger']->debug(
                    sprintf('Loaded template route: %s', $templateIdent),
                    $templateConfig
                );

                if (!isset($templateConfig['template_data'])) {
                    $templateConfig['template_data'] = [];
                }

                if (count($args)) {
                    $templateConfig['template_data'] = array_merge(
                        $templateConfig['template_data'],
                        $args
                    );
                }

                $routeFactory = $this['route/factory'];
                $defaultRoute = 'charcoal/app/route/template';
                $routeController = isset($templateConfig['route_controller'])
                    ? $templateConfig['route_controller']
                    : $defaultRoute;

                $route = $routeFactory->create($routeController, [
                    'config' => $templateConfig,
                    'logger' => $this['logger']
                ]);

                return $route($this, $request, $response);
            }
        );

        if (isset($templateConfig['ident'])) {
            $routeHandler->setName($templateConfig['ident']);
        }

        if (isset($templateConfig['template_data'])) {
            $routeHandler->setArguments($templateConfig['template_data']);
        }
    }

    /**
     * Add action route.
     *
     * Typically for a POST request, the route will execute an action (returns JSON).
     *
     * @param  string             $routeIdent   The action's route identifier.
     * @param  array|\ArrayAccess $actionConfig The action's config for the route.
     * @throws InvalidArgumentException If the route identifier is not a string.
     * @return void
     */
    public function setupAction($routeIdent, $actionConfig)
    {
        if (!is_string($routeIdent)) {
            throw new InvalidArgumentException(
                'Can not setup route action, route identifier is not a string'
            );
        }

        $actionIdent = isset($actionConfig['ident'])
            ? $actionConfig['ident']
            : $routeIdent;

        $actionIdent = ltrim($actionIdent, '/');
        $actionConfig['ident'] = $actionIdent;

        $routePattern = isset($actionConfig['route'])
            ? $actionConfig['route']
            : $routeIdent;

        $routePattern = '/'.ltrim($routePattern, '/');
        $actionConfig['route'] = $routePattern;

        $methods = isset($actionConfig['methods'])
            ? $actionConfig['methods']
            : [ 'POST' ];

        $routeHandler = $this->app()->map(
            $methods,
            $routePattern,
            function (
                RequestInterface $request,
                ResponseInterface $response,
                array $args = []
            ) use (
                $actionIdent,
                $actionConfig
            ) {
                $this['logger']->debug(
                    sprintf('Loaded action route: %s', $actionIdent),
                    $actionConfig
                );

                if (!isset($actionConfig['action_data'])) {
                    $actionConfig['action_data'] = [];
                }

                if (count($args)) {
                    $actionConfig['action_data'] = array_merge(
                        $actionConfig['action_data'],
                        $args
                    );
                }

                $routeFactory = $this['route/factory'];
                $defaultRoute = 'charcoal/app/route/action';
                $routeController = isset($actionConfig['route_controller'])
                    ? $actionConfig['route_controller']
                    : $defaultRoute;

                $route = $routeFactory->create($routeController, [
                    'config' => $actionConfig,
                    'logger' => $this['logger']
                ]);

                return $route($this, $request, $response);
            }
        );

        if (isset($actionConfig['ident'])) {
            $routeHandler->setName($actionConfig['ident']);
        }

        if (isset($actionConfig['action_data'])) {
            $routeHandler->setArguments($actionConfig['action_data']);
        }
    }

    /**
     * Add script route.
     *
     * Typically used for a CLI interface, the route will execute a script.
     *
     * @param  string             $routeIdent   The script's route identifier.
     * @param  array|\ArrayAccess $scriptConfig The script's config for the route.
     * @throws InvalidArgumentException If the route identifier is not a string.
     * @return void
     */
    public function setupScript($routeIdent, $scriptConfig)
    {
        if (!is_string($routeIdent)) {
            throw new InvalidArgumentException(
                'Can not setup route script, route identifier is not a string'
            );
        }

        $scriptIdent = isset($scriptConfig['ident'])
            ? $scriptConfig['ident']
            : $routeIdent;

        $scriptIdent = ltrim($scriptIdent, '/');
        $scriptConfig['ident'] = $scriptIdent;

        $routePattern = isset($scriptConfig['route'])
            ? $scriptConfig['route']
            : $routeIdent;

        $routePattern = '/'.ltrim($routePattern, '/');
        $scriptConfig['route'] = $routePattern;

        $methods = isset($scriptConfig['methods'])
            ? $scriptConfig['methods']
            : [ 'GET' ];

        $routeHandler = $this->app()->map(
            $methods,
            $routePattern,
            function (
                RequestInterface $request,
                ResponseInterface $response,
                array $args = []
            ) use (
                $scriptIdent,
                $scriptConfig
            ) {
                $this->logger->debug(
                    sprintf('Loaded script route: %s', $scriptIdent),
                    $scriptConfig
                );

                if (!isset($scriptConfig['script_data'])) {
                    $scriptConfig['script_data'] = [];
                }

                if (count($args)) {
                    $scriptConfig['script_data'] = array_merge(
                        $scriptConfig['script_data'],
                        $args
                    );
                }

                $routeFactory = $this['route/factory'];
                $defaultRoute = 'charcoal/app/route/script';
                $routeController = isset($scriptConfig['route_controller'])
                    ? $scriptConfig['route_controller']
                    : $defaultRoute;

                $route = $routeFactory->create($routeController, [
                    'config' => $scriptConfig,
                    'logger' => $this['logger']
                ]);

                return $route($this, $request, $response);
            }
        );

        if (isset($scriptConfig['ident'])) {
            $routeHandler->setName($scriptConfig['ident']);
        }

        if (isset($scriptConfig['script_data'])) {
            $routeHandler->setArguments($scriptConfig['script_data']);
        }
    }
}
