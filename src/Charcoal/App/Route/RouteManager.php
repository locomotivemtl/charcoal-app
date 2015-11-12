<?php

namespace Charcoal\App\Route;

use \Exception;
use \InvalidArgumentException;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// Local namespace dependencies
use \Charcoal\App\Route\ActionRoute;
use \Charcoal\App\Route\ScriptRoute;
use \Charcoal\App\Route\TemplateRoute;

class RouteManager implements LoggerAwareInterface
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
    * @throws InvalidArgumentException
    */
    public function __construct($data)
    {
        $this->config = $data['config'];
        $this->app = $data['app'];
        if (!($this->app instanceof \Slim\App)) {
            throw new InvalidArgumentException(
                'RouteManager requires a Slim App object in its dependency container.'
            );
        }

        $logger = isset($data['logger']) ? $data['logger'] : $this->app->logger;
        $this->set_logger($data['logger']);
    }

        /**
    * > LoggerAwareInterface > setLogger()
    *
    * Fulfills the PSR-1 style LoggerAwareInterface
    *
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function setLogger(LoggerInterface $logger)
    {
        return $this->set_logger($logger);
    }

    /**
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function set_logger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
    * @erturn LoggerInterface
    */
    public function logger()
    {
        return $this->logger;
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

        //$this->setup_script_routes();
    }

    /**
    * @throws Exception
    * @return void
    */
    protected function setup_template_routes()
    {
        $routes = $this->config;

        $templates = isset($routes['templates']) ? $routes['templates'] : [];
        $this->logger->debug('Templates', (array)$templates);
        foreach ($templates as $template_ident => $template_config) {
            $methods = isset($tempate_config['methods']) ? $template_config['methods'] : ['GET'];
            $this->app->map(
                $methods,
                '/'.$template_ident,
                function($request, $response, $args) use ($template_ident, $template_config) {
                    if (!isset($template_config['ident'])) {
                        $template_config['ident'] = $template_ident;
                    }
                    $route = new TemplateRoute([
                         'app' => $this,
                         'config' => $template_config
                    ]);

                    // Inboke template route
                    return $route($request, $response);
                }
            );
        }

        // Set up default template route
        $default_template = isset($routes['default_template']) ? $routes['default_template'] : null;
        if ($default_template) {
            if (!isset($templates[$default_template])) {
                throw new Exception(
                    sprintf('Default template "%s" is not defined.', $default_template)
                );
            }
            $default_template_config = $templates[$default_template];
            $methods = isset($default_template_config['methods']) ? $default_template_config['methods'] : ['GET'];
            $this->app->map(
                $methods,
                '/',
                function($request, $response, $args) use ($default_template, $default_template_config) {
                    if (!isset($template_config['ident'])) {
                        $default_template_config['ident'] = $default_template;
                    }
                    $default_route = new TemplateRoute([
                        'app' => $this,
                        'config', $default_template_config
                    ]);

                    // Invoke default template route
                    return $default_route($request, $response);
                }
            );
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
            $this->app->map(
                $methods,
                '/'.$action_ident,
                function($request, $response, $args) use ($action_ident, $action_config) {
                    if (!isset($action_config['ident'])) {
                        $action_config['ident'] = $action_ident;
                    }
                    $route = new ActionRoute([
                        'app' => $this,
                        'config' => $action_config
                    ]);

                    // Inoke action route
                    return $route($request, $response);
                }
            );
        }
    }

    /**
    * @return void
    */
    protected function setup_script_routes()
    {
        $routes = $this->config;

        $scripts = isset($routes['scripts']) ? $routes['scripts'] : [];
        foreach ($scripts as $script_ident => $script_config) {
            $methods = isset($script_config['methods']) ? $script_config['methods'] : ['GET
            '];
            $this->app->map(
                $methods,
                '/'.$script_ident,
                function($request, $response, $args) use ($script_ident, $script_config) {
                    if (!isset($script_config['ident'])) {
                        $script_config['ident'] = $script_ident;
                    }
                    $route = new ScriptRoute([
                        'app' => $this->app,
                        'config' => $script_config
                    ]);
                    return $route($request, $response);
                }
            );
        }
    }
}
