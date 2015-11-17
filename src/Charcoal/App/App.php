<?php

namespace Charcoal\App;

// PHP Dependencies
use \Exception;

// slim/slim dependencies
use \Slim\App as SlimApp;

// PSR-3 Logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;

use \Charcoal\App\Middleware\MiddlewareManager;
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;
use \Charcoal\App\Routable\RoutableFactory;

/**
* ## Dependencies
* - **config** (`\Charcoal\App\AppConfig`)
* - **app** (`SlimApp`)
*/
class App implements
    AppInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * @var SlimApp $app
    */
    private $app;

    /**
    * @var LoggerInterface
    */
    private $logger;

    /**
    * # Required dependencies
    * - `logger` A PSR-3 logger.
    *
    * @param array $data
    */
    public function __construct(array $data)
    {
        $this->logger = $data['app']->logger;
        $this->logger->debug('Charcoal App Init logger');

        $this->set_config($data['config']);
        $this->set_app($data['app']);
    }

    /**
    * > LoggerAwareInterface > setLogger()
    *
    * Fulfills the PSR-1 / PSR-3 style LoggerAwareInterface
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
    * @param SlimApp $app
    * @return App Chainable
    */
    public function set_app(SlimApp $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
    * @return App Chainable
    */
    public function setup()
    {
        $this->setup_middlewares();
        $this->setup_routes();
        $this->setup_routables();
        $this->setup_modules();
        return $this;
    }

    /**
    * @return void
    */
    protected function setup_middlewares()
    {
        $config = $this->config();
        $middlewares = $config['middlewares'];
        if ($middlewares === null || count($middlewares) === 0) {
            return;
        }
        $middleware_manager = new MiddlewareManager([
            'config' => $middlewares,
            'app' => $this->app,
            'logger' => $this->logger
        ]);
        return $middleware_manager->setup_middlewares();
    }

    /**
    * Set up the app's "global" routes, via a RouteManager
    *
    * @return void
    */
    protected function setup_routes()
    {
        $config = $this->config();
        $routes = $config['routes'];
        if ($routes === null || count($routes) === 0) {
            return;
        }
        $route_manager = new RouteManager([
            'config' => $routes,
            'app' => $this->app,
            'logger' => $this->logger
        ]);
        return $route_manager->setup_routes();
    }

    /**
    * Set up the app's "global" routables.
    * Routables can only be defined globally (app-level) for now.
    *
    * @return void
    */
    protected function setup_routables()
    {
        $charcoal = $this;
        // For now, need to rely on a catch-all...
        $this->app->get('{catchall:.*}', function(RequestInterface $request, ResponseInterface $response, $args) use ($charcoal) {

            $config = $charcoal->config();
            $routables = $config['routables'];
            if ($routables === null || count($routables) === 0) {
                return;
            }
            foreach ($routables as $routable_type => $routable_options) {
                $routable = RoutableFactory::instance()->create($routable_type);
                $route = $routable->handle_route($args['catchall'], $request, $response);
                if ($route) {
                    return $route($request, $response);
                }
            }

            // If this point is reached, no routable has provided a callback. 404.
            return $this->notFoundHandler($request, $response);
        });
    }

    /**
    * @return void
    */
    protected function setup_modules()
    {
        $config = $this->config();
        $modules = $config['modules'];
        if ($modules === null || count($modules) === 0) {
            return;
        }
        
        $module_manager = new ModuleManager([
            'modules' => $modules,
            'app' => $this->app,
            'logger' => $this->logger
        ]);
        return $module_manager->setup_modules();
    }


    /**
    * ConfigurableTrait > create_config()
    *
    * @param array $data
    * @return AppConfig
    */
    public function create_config($data = null)
    {
        return new AppConfig($data);
    }
}
