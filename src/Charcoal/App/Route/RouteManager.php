<?php

namespace Charcoal\App\Route;

// Local namespace dependencies
use \Charcoal\App\Route\ActionRoute;
use \Charcoal\App\Route\ScriptRoute;
use \Charcoal\App\Route\TemplateRoute;

class RouteManager
{
    /**
    * @var array $config
    */
    private $config = [];

    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
    * PSR-1 Logger
    * @var \Psr\Log\LoggerInterface $logger
    */
    private $logger;

    /**
    * @param array $data The dependencies container
    */
    public function __construct($data)
    {
        $this->config = $data['config'];
        $this->app = $data['app'];
        $this->logger = $data['logger'];
    }

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
        $this->setup_template_routes();
        $this->setup_action_routes();
        $this->setup_script_routes();
    }

    /**
    * @return void
    */
    protected function setup_template_routes()
    {
        $routes = $this->config;

        $templates = isset($routes['templates']) ? $routes['templates'] : [];
        $this->logger->debug('Templates', (array)$templates);
        foreach ($templates as $template_ident => $template_config) {
            $methods = isset($tempate_config['methods']) ? $template_config['methods'] : ['GET'];
            $this->app->map($methods, '/'.$template_ident, function($request, $response, $args) use ($template_ident, $template_config) {
                if (!isset($template_config['ident'])) {
                    $template_config['ident'] = $template_ident;
                }
                $route = new TemplateRoute([
                     'app' => $this,
                     'config' => $template_config
                ]);
                return $route($request, $response);

            });
        }
    }

    /**
    * @return void
    */
    protected function setup_action_routes()
    {
        $routes = $this->config;

        $actions = isset($routes['actions']) ? $routes['actions'] : [];
        foreach ($actions as $action_ident => $action_config) {
            $methods = isset($action_config['methods']) ? $action_config['methods'] : ['POST
            '];
            $this->app->map($methods, '/'.$action_ident, function($request, $response, $args) use ($action_ident, $action_config) {
                if (!isset($action_config['ident'])) {
                    $action_config['ident'] = $action_ident;
                }
                $route = new ActionRoute([
                    'app' => $this,
                    'config' => $action_config
                ]);
                return $route($request, $response);
            });
        }
    }

    /**
    * @return void
    */
    protected function setup_script_routes()
    {
        $routes = $this->config;
        $logger = $this->logger;
        $app = $this->app;

        $scripts = isset($routes['scripts']) ? $routes['scripts'] : [];
        foreach ($scripts as $script_ident => $script_config) {
            $methods = isset($script_config['methods']) ? $script_config['methods'] : ['GET
            '];
            $this->app->map($methods, '/', $script_ident, function($request, $response, $args) use ($script_ident, $script_config) {
                if (!isset($script_config['ident'])) {
                    $script_config['ident'] = $script_ident;
                }
                $route = new ScriptRoute([
                    'app' => $this->app,
                    'config' => $script_config
                ]);
                return $route($request, $response);
            });
            
        }
    }
}
