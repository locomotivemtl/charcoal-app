<?php

namespace Charcoal\App\Route;

// PSR-7 (http messaging) dependencies
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Module `charcoal-config` dependencies
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use Charcoal\App\AppAwareInterface;
use Charcoal\App\AppAwareTrait;
use Charcoal\App\Route\ActionRoute;
use Charcoal\App\Route\ScriptRoute;
use Charcoal\App\Route\TemplateRoute;

/**
 * The route manager takes care of dispatching each route from an app or a module config
 */
class RouteManager implements
    AppAwareInterface,
    ConfigurableInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;

    /**
     * Additional route data.
     *
     * @var array
     */
    private $routeData = [];

    /**
     * Manager constructor
     *
     * @param array $data The dependencies container.
     */
    public function __construct(array $data)
    {
        $this->setConfig($data['config']);
        $this->setApp($data['app']);
    }

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
    private function setupTemplate($routeIdent, $templateConfig)
    {
        if (!isset($templateConfig['ident'])) {
            $templateConfig['ident'] = ltrim($routeIdent, '/');
        }
        if (!isset($templateConfig['route'])) {
            $templateConfig['route'] = '/'.$templateConfig['ident'];
        }
        if (!isset($templateConfig['methods'])) {
            $templateConfig['methods'] = ['GET'];
        }

        $routeCallback = function (
            RequestInterface $request,
            ResponseInterface $response,
            array $args = []
        ) use (
            $templateConfig
        ) {
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
            $defaultRoute = TemplateRoute::class;
            $routeController = isset($templateConfig['route_controller'])
                ? $templateConfig['route_controller']
                : $defaultRoute;

            $route = $routeFactory->create($routeController, [
                'config' => $templateConfig,
                'logger' => $this['logger']
            ]);

            return $route($this, $request, $response);
        };

        $routeHandler = $this->app()->map(
            $templateConfig['methods'],
            $templateConfig['route'],
            $routeCallback
        )->setName($templateConfig['ident']);

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
    private function setupAction($routeIdent, $actionConfig)
    {
        if (!isset($actionConfig['ident'])) {
            $actionConfig['ident'] = ltrim($routeIdent, '/');
        }
        if (!isset($actionConfig['route'])) {
            $actionConfig['route'] = '/'.$actionConfig['ident'];
        }
        if (!isset($actionConfig['methods'])) {
            $actionConfig['methods'] = ['POST'];
        }

        $routeCallback = function (
            RequestInterface $request,
            ResponseInterface $response,
            array $args = []
        ) use (
            $routeIdent,
            $actionConfig
        ) {
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
            $defaultRoute = ActionRoute::class;
            $routeController = isset($actionConfig['route_controller'])
                ? $actionConfig['route_controller']
                : $defaultRoute;

            $route = $routeFactory->create($routeController, [
                'config' => $actionConfig,
                'logger' => $this['logger']
            ]);

            return $route($this, $request, $response);
        };

        $routeHandler = $this->app()->map(
            $actionConfig['methods'],
            $actionConfig['route'],
            $routeCallback
        )->setName($actionConfig['ident']);

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
    private function setupScript($routeIdent, $scriptConfig)
    {
        if (!isset($scriptConfig['ident'])) {
            $scriptConfig['ident'] = ltrim($routeIdent, '/');
        }
        if (!isset($scriptConfig['route'])) {
            $scriptConfig['route'] = '/'.$scriptConfig['ident'];
        }
        if (!isset($scriptConfig['methods'])) {
            $scriptConfig['methods'] = ['GET'];
        }

        $routeCallback = function (
            RequestInterface $request,
            ResponseInterface $response,
            array $args = []
        ) use (
            $routeIdent,
            $scriptConfig
        ) {
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
            $defaultRoute = ScriptRoute::class;
            $routeController = isset($scriptConfig['route_controller'])
                ? $scriptConfig['route_controller']
                : $defaultRoute;

            $route = $routeFactory->create($routeController, [
                'config' => $scriptConfig,
                'logger' => $this['logger']
            ]);

            return $route($this, $request, $response);
        };

        $routeName = isset($scriptConfig['ident']) ? $scriptConfig['ident'] : $templateConfig['route'];

        $routeHandler = $this->app()->map(
            $scriptConfig['methods'],
            $scriptConfig['route'],
            $routeCallback
        )->setName($scriptConfig['ident']);

        return $routeHandler;
    }

}
