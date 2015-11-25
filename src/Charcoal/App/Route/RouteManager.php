<?php

namespace Charcoal\App\Route;

use \Exception;
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;
use \Charcoal\App\Route\ActionRoute;
use \Charcoal\App\Route\ScriptRoute;
use \Charcoal\App\Route\TemplateRoute;
use \Charcoal\App\Route\TemplateRouteConfig;

/**
*
*/
class RouteManager extends AbstractManager
{
    /**
    * Set up the routes
    *
    * There are 3 types of routes:
    * - `templates`
    * - `actions`
    * - `scripts
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
    * @throws Exception
    * @return void
    */
    protected function setup_template_routes()
    {
        $routes = $this->config();

        $templates = ( isset($routes['templates']) ? $routes['templates'] : [] );
        $this->logger()->debug('Templates', (array)$templates);
        foreach ($templates as $template_ident => $template_config) {
            $methods = ( isset($tempate_config['methods']) ? $template_config['methods'] : [ 'GET' ] );

            $route_handler = $this->app()->map(
                $methods,
                $template_ident,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    $args
                ) use (
                    $template_ident,
                    $template_config
                ) {
                    if (!isset($template_config['ident'])) {
                        $template_config['ident'] = ltrim($template_ident, '/');
                    }

                    $route = new TemplateRoute([
                         'app'    => $this,
                         'config' => new TemplateRouteConfig($template_config)
                    ]);
                    // Invoke template route
                    return $route($request, $response);
                }
            );

            if (isset($template_config['ident'])) {
                $route_handler->setName($template_config['ident']);
            }
        }
    }

    /**
    * @return void
    */
    protected function setup_action_routes()
    {
        $routes = $this->config();

        $actions = ( isset($routes['actions']) ? $routes['actions'] : [] );
        $this->logger()->debug('Actions', (array)$actions);
        foreach ($actions as $action_ident => $action_config) {
            $methods = ( isset($action_config['methods']) ? $action_config['methods'] : [ 'POST' ] );

            $route = $this->app()->map(
                $methods,
                $action_ident,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    $args
                ) use (
                    $action_ident,
                    $action_config
                ) {
                    if (!isset($action_config['ident'])) {
                        $action_config['ident'] = ltrim($action_ident, '/');
                    }

                    $route = new ActionRoute([
                        'app'    => $this,
                        'config' => $action_config // new ActionRouteConfig($action_config)
                    ]);

                    return $route($request, $response);
                }
            );

            if (isset($action_config['ident'])) {
                $route->setName($action_config['ident']);
            }
        }
    }

    /**
    * @return void
    */
    protected function setup_script_routes()
    {
        $routes = $this->config();

        $scripts = ( isset($routes['scripts']) ? $routes['scripts'] : [] );
        $this->logger()->debug('Scripts', (array)$scripts);
        foreach ($scripts as $script_ident => $script_config) {
            $methods = ( isset($script_config['methods']) ? $script_config['methods'] : [ 'GET' ] );

            $route = $this->app()->map(
                $methods,
                $script_ident,
                function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    $args
                ) use (
                    $script_ident,
                    $script_config
                ) {
                    if (!isset($script_config['ident'])) {
                        $script_config['ident'] = ltrim($script_ident, '/');
                    }

                    $route = new ScriptRoute([
                        'app'    => $this->app,
                        'config' => $script_config // new ScriptRouteConfig($script_config)
                    ]);

                    return $route($request, $response);
                }
            );

            if (isset($script_config['ident'])) {
                $route->setName($script_config['ident']);
            }
        }
    }
}
