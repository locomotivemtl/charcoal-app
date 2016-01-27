<?php

namespace Charcoal\App\Route;

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
     * Set up the routes
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
        if (PHP_SAPI == 'cli') {
            $this->setupScriptRoutes();
        } else {
            $this->setupTemplateRoutes();
            $this->setupActionRoutes();
        }
    }

    /**
     * @return void
     */
    protected function setupTemplateRoutes()
    {
        $app       = $this->app();
        $routes    = $this->config();
        $templates = ( isset($routes['templates']) ? $routes['templates'] : [] );

        foreach ($templates as $templateIdent => $tplConfig) {
            $templateIdent = ltrim($templateIdent, '/');

            if (!isset($tplConfig['ident'])) {
                $tplConfig['ident'] = $templateIdent;
            }

            if (isset($tplConfig['route'])) {
                $routeIdent = '/'.ltrim($tplConfig['route'], '/');
            } else {
                $routeIdent = '/'.$templateIdent;
                $tplConfig['route'] = $routeIdent;
            }

            if (isset($tplConfig['methods'])) {
                $methods = $tplConfig['methods'];
            } else {
                $methods = ['GET'];
            }

            $routeHandler = $app->map(
                $methods,
                $routeIdent,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    array $args = []
                ) use (
                    $app,
                    $templateIdent,
                    $tplConfig
                ) {
                    $this['logger']->debug(
                        sprintf('Loaded template route: %s', $templateIdent),
                        $tplConfig
                    );

                    if (!isset($tplConfig['template_data'])) {
                        $tplConfig['template_data'] = [];
                    }

                    if (count($args)) {
                        $tplConfig['template_data'] = array_merge(
                            $tplConfig['template_data'],
                            $args
                        );
                    }

                    $routeFactory = $this['route/factory'];
                    $defaultRoute = 'charcoal/app/route/template';
                    $routeController = isset($tplConfig['route_controller'])
                        ? $tplConfig['route_controller']
                        : $defaultRoute;

                    $route = $routeFactory->create($routeController, [
                        'app'    => $app,
                        'config' => $tplConfig,
                        'logger' => $this['logger']
                    ]);

                    return $route($this, $request, $response);
                }
            );

            if (isset($tplConfig['ident'])) {
                $routeHandler->setName($tplConfig['ident']);
            }

            if (isset($tplConfig['template_data'])) {
                $routeHandler->setArguments($tplConfig['template_data']);
            }
        }
    }

    /**
     * @return void
     */
    protected function setupActionRoutes()
    {
        $app     = $this->app();
        $routes  = $this->config();
        $actions = ( isset($routes['actions']) ? $routes['actions'] : [] );

        foreach ($actions as $actionIdent => $actionConfig) {
            $actionIdent = ltrim($actionIdent, '/');

            if (!isset($actionConfig['ident'])) {
                $actionConfig['ident'] = $actionIdent;
            }

            if (isset($actionConfig['route'])) {
                $routeIdent = '/'.ltrim($actionConfig['route'], '/');
            } else {
                $routeIdent = '/'.$actionIdent;
                $actionConfig['route'] = $routeIdent;
            }

            if (isset($actionConfig['methods'])) {
                $methods = $actionConfig['methods'];
            } else {
                $methods = ['POST'];
            }

            $routeHandler = $app->map(
                $methods,
                $routeIdent,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    array $args = []
                ) use (
                    $app,
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
                        'app'    => $app,
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
    }

    /**
     * @return void
     */
    protected function setupScriptRoutes()
    {
        $app     = $this->app();
        $routes  = $this->config();
        $scripts = ( isset($routes['scripts']) ? $routes['scripts'] : [] );

        foreach ($scripts as $scriptIdent => $scriptConfig) {
            $scriptIdent = ltrim($scriptIdent, '/');

            if (!isset($scriptConfig['ident'])) {
                $scriptConfig['ident'] = $scriptIdent;
            }

            if (isset($scriptConfig['route'])) {
                $routeIdent = '/'.ltrim($scriptConfig['route'], '/');
            } else {
                $routeIdent = '/'.$scriptIdent;
                $scriptConfig['route'] = $routeIdent;
            }

            if (isset($scriptConfig['methods'])) {
                $methods = $scriptConfig['methods'];
            } else {
                $methods = ['GET'];
            }

            $routeHandler = $app->map(
                $methods,
                $routeIdent,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    array $args = []
                ) use (
                    $app,
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
                        'app'    => $app,
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
}
