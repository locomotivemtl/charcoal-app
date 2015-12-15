<?php

namespace Charcoal\App\Route;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;
use \Charcoal\App\Route\ActionRoute;
use \Charcoal\App\Route\ScriptRoute;
use \Charcoal\App\Route\TemplateRoute;

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
    public function setup_routes()
    {
        if (PHP_SAPI == 'cli') {
            $this->setup_script_routes();
        } else {
            $this->setup_template_routes();
            $this->setup_action_routes();
        }
    }

    /**
     * @return void
     */
    protected function setup_template_routes()
    {
        $app       = $this->app();
        $routes    = $this->config();
        $templates = ( isset($routes['templates']) ? $routes['templates'] : [] );

        $this->logger()->debug('Templates', (array)$templates);
        foreach ($templates as $template_ident => $template_config) {
            $route_ident = '/'.ltrim($template_ident, '/');
            $methods = ( isset($tempate_config['methods']) ? $template_config['methods'] : [ 'GET' ] );
            $route_handler = $app->map(
                $methods,
                $route_ident,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    array $args = []
                ) use (
                    $app,
                    $template_ident,
                    $template_config
                ) {
                    $this->logger->debug(
                        sprintf('Loaded template route: %s', $template_ident),
                        $template_config
                    );

                    if (!isset($template_config['ident'])) {
                        $template_config['ident'] = ltrim($template_ident, '/');
                    }

                    if (!isset($template_config['template_data'])) {
                        $template_config['template_data'] = [];
                    }

                    if (count($args)) {
                        $template_config['template_data'] = array_merge(
                            $template_config['template_data'],
                            $args
                        );
                    }

                    $route = new TemplateRoute([
                        'app'    => $app,
                        'config' => $template_config,
                        'logger' => $this->logger
                    ]);

                    return $route($request, $response);
                }
            );

            if (isset($template_config['ident'])) {
                $route_handler->setName($template_config['ident']);
            }

            if (isset($template_config['template_data'])) {
                $route_handler->setArguments($template_config['template_data']);
            }
        }
    }

    /**
     * @return void
     */
    protected function setup_action_routes()
    {
        $app     = $this->app();
        $routes  = $this->config();
        $actions = ( isset($routes['actions']) ? $routes['actions'] : [] );

        $this->logger()->debug('Actions', (array)$actions);
        foreach ($actions as $action_ident => $action_config) {
            $route_ident = '/'.ltrim($action_ident, '/');
            $methods = ( isset($action_config['methods']) ? $action_config['methods'] : [ 'POST' ] );
            $route_handler = $app->map(
                $methods,
                $route_ident,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    array $args = []
                ) use (
                    $app,
                    $action_ident,
                    $action_config
                ) {
                    $this->logger->debug(
                        sprintf('Loaded action route: %s', $action_ident),
                        $action_config
                    );

                    if (!isset($action_config['ident'])) {
                        $action_config['ident'] = ltrim($action_ident, '/');
                    }

                    if (!isset($action_config['template_data'])) {
                        $action_config['action_data'] = [];
                    }

                    if (count($args)) {
                        $action_config['action_data'] = array_merge(
                            $action_config['action_data'],
                            $args
                        );
                    }

                    $route = new ActionRoute([
                        'app'    => $app,
                        'config' => $action_config,
                        'logger' => $this->logger
                    ]);

                    return $route($request, $response);
                }
            );

            if (isset($action_config['ident'])) {
                $route_handler->setName($action_config['ident']);
            }

            if (isset($action_config['action_data'])) {
                $route_handler->setArguments($action_config['action_data']);
            }
        }
    }

    /**
     * @return void
     */
    protected function setup_script_routes()
    {
        $app     = $this->app();
        $routes  = $this->config();
        $scripts = ( isset($routes['scripts']) ? $routes['scripts'] : [] );

        $this->logger()->debug('Scripts', (array)$scripts);
        foreach ($scripts as $script_ident => $script_config) {
            $route_ident = '/'.ltrim($script_ident, '/');
            $methods = ( isset($script_config['methods']) ? $script_config['methods'] : [ 'GET' ] );
            $route_handler = $app->map(
                $methods,
                $route_ident,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    array $args = []
                ) use (
                    $app,
                    $script_ident,
                    $script_config
                ) {
                    $this->logger->debug(
                        sprintf('Loaded script route: %s', $script_ident),
                        $script_config
                    );

                    if (!isset($script_config['ident'])) {
                        $script_config['ident'] = ltrim($script_ident, '/');
                    }

                    if (!isset($script_config['script_data'])) {
                        $script_config['script_data'] = [];
                    }

                    if (count($args)) {
                        $script_config['script_data'] = array_merge(
                            $script_config['script_data'],
                            $args
                        );
                    }

                    $route = new ScriptRoute([
                        'app'    => $app,
                        'config' => $script_config,
                        'logger' => $this->logger
                    ]);

                    return $route($request, $response);
                }
            );

            if (isset($script_config['ident'])) {
                $route_handler->setName($script_config['ident']);
            }

            if (isset($script_config['script_data'])) {
                $route_handler->setArguments($script_config['script_data']);
            }
        }
    }
}
