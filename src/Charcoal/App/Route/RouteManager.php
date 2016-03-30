<?php

namespace Charcoal\App\Route;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;

/**
 * The route manager takes care of dispatching each route from an app or a module config
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
     * @return \Slim\Route
     */
    public function setupTemplate($routeIdent, $templateConfig)
    {
        $routePattern = isset($templateConfig['route'])
            ? $templateConfig['route']
            : '/'.ltrim($routeIdent, '/');

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
                $routeIdent,
                $templateConfig
            ) {
                $templateIdent = isset($templateConfig['ident'])
                    ? $templateConfig['ident']
                    : $routeIdent;

                $this['logger']->debug(
                    sprintf('Loaded template route: %s', $routeIdent),
                    $templateConfig
                );

                $templateIdent = ltrim($templateIdent, '/');
                $templateConfig['ident'] = $templateIdent;

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

        return $routeHandler;
    }

    /**
     * Add action route.
     *
     * Typically for a POST request, the route will execute an action (returns JSON).
     *
     * @param  string             $routeIdent   The action's route identifier.
     * @param  array|\ArrayAccess $actionConfig The action's config for the route.
     * @return \Slim\Route
     */
    public function setupAction($routeIdent, $actionConfig)
    {
        $routePattern = isset($actionConfig['route'])
            ? $actionConfig['route']
            : '/'.ltrim($routeIdent, '/');

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
                $routeIdent,
                $actionConfig
            ) {
                $actionIdent = isset($actionConfig['ident'])
                    ? $actionConfig['ident']
                    : $routeIdent;

                $this['logger']->debug(
                    sprintf('Loaded action route: %s', $routeIdent),
                    $actionConfig
                );

                $actionIdent = ltrim($actionIdent, '/');
                $actionConfig['ident'] = $actionIdent;

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

        return $routeHandler;
    }

    /**
     * Add script route.
     *
     * Typically used for a CLI interface, the route will execute a script.
     *
     * @param  string             $routeIdent   The script's route identifier.
     * @param  array|\ArrayAccess $scriptConfig The script's config for the route.
     * @return \Slim\Route
     */
    public function setupScript($routeIdent, $scriptConfig)
    {
        $routePattern = isset($scriptConfig['route'])
            ? $scriptConfig['route']
            : '/'.ltrim($routeIdent, '/');

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
                $routeIdent,
                $scriptConfig
            ) {
                $scriptIdent = isset($scriptConfig['ident'])
                    ? $scriptConfig['ident']
                    : $routeIdent;

                $this->logger->debug(
                    sprintf('Loaded script route: %s', $routeIdent),
                    $scriptConfig
                );

                $scriptIdent = ltrim($scriptIdent, '/');
                $scriptConfig['ident'] = $scriptIdent;

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
        return $routeHandler;
    }
}
