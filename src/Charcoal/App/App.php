<?php

namespace Charcoal\App;

// PHP Dependencies
use \Exception;

// slim/slim dependencies
use \Slim\App as SlimApp;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;
use \Charcoal\App\SingletonInterface;
use \Charcoal\App\SingletonTrait;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;

use \Charcoal\App\Language\LanguageManager;
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
    SingletonInterface,
    ConfigurableInterface
{
    use SingletonTrait;
    use LoggerAwareTrait;
    use ConfigurableTrait;

    /**
    * @var SlimApp $app
    */
    private $app;

    /**
    * @var ModuleManager
    */
    private $module_manager;

    /**
    * @var RouteManager
    */
    private $route_manager;

    /**
    * @var MiddlewareManager
    */
    private $middleware_manager;

    /**
    * @var LanguageManager
    */
    private $language_manager;

    /**
    * # Required dependencies
    * - `logger` A PSR-3 logger.
    *
    * @param array $data
    */
    public function __construct(array $data)
    {
        $this->set_logger($data['app']->logger);
        $this->logger()->debug('Charcoal App Init logger');

        $this->set_config($data['config']);
        $this->set_app($data['app']);
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
    * @return ModuleManager
    */
    public function module_manager()
    {
        if ($this->module_manager === null) {
            $config  = $this->config();
            $modules = (isset($config['modules']) ? $config['modules'] : [] );
            $this->module_manager = new ModuleManager([
                'config' => $modules,
                'app'    => $this->app,
                'logger' => $this->logger()
            ]);
        }
        return $this->module_manager;
    }

    /**
    * @return RouteManager
    */
    public function route_manager()
    {
        if ($this->route_manager === null) {
            $config = $this->config();
            $routes = (isset($config['routes']) ? $config['routes'] : [] );
            $route_manager = new RouteManager([
                'config' => $routes,
                'app'    => $this->app,
                'logger' => $this->logger()
            ]);
        }
        return $route_manager;
    }

    /**
    * @return MiddlewareManager
    */
    public function middleware_manager()
    {
        if ($this->middleware_manager === null) {
            $config = $this->config();
            $middlewares = (isset($config['middlewares']) ? $config['middlewares'] : [] );
            $middleware_manager = new MiddlewareManager([
                'config' => $middlewares,
                'app'    => $this->app,
                'logger' => $this->logger()
            ]);
        }
        return $middleware_manager;
    }

    /**
    * @return LanguageManager
    */
    public function language_manager()
    {
        if (!isset($this->language_manager)) {
            $config = $this->config();

            $locales = [];
            if (isset($config['locales'])) {
                $locales = $config['locales'];
            } elseif (isset($config['languages'])) {
                $locales['languages'] = $config['languages'];

                if (isset($config['default_language'])) {
                    $locales['default_language'] = $config['default_language'];
                }
            }

            $language_manager = new LanguageManager([
                'config' => $locales,
                'app'    => $this->app,
                'logger' => $this->logger()
            ]);
        }
        return $language_manager;
    }

    /**
    * @return App Chainable
    */
    public function setup()
    {
        //$this->setup_languages();
        $this->setup_middlewares();
        $this->setup_routes();
        $this->setup_modules();
        $this->setup_routables();
        return $this;
    }

    /**
    * @return void
    */
    protected function setup_languages()
    {
        $language_manager = $this->language_manager();
        return $language_manager->setup();
    }

    /**
    * @return void
    */
    protected function setup_middlewares()
    {
        $middleware_manager = $this->middleware_manager();
        return $middleware_manager->setup_middlewares();
    }

    /**
    * Set up the app's "global" routes, via a RouteManager
    *
    * @return void
    */
    protected function setup_routes()
    {
        $route_manager = $this->route_manager();
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
        $this->app->get(
            '{catchall:.*}',
            function (
                RequestInterface $request,
                ResponseInterface $response,
                $args
            ) use ($charcoal) {
                $config = $charcoal->config();
                $routables = $config['routables'];
                if ($routables === null || count($routables) === 0) {
                    //return $this->notFoundHandler($request, $response);
                    return $response->write('No routable defined.');
                }
                foreach ($routables as $routable_type => $routable_options) {
                    $routable = RoutableFactory::instance()->create($routable_type);
                    $route = $routable->route_handler($args['catchall'], $request, $response);
                    if ($route) {
                        return $route($request, $response);
                    }
                }

                // If this point is reached, no routable has provided a callback. 404.
                return $this->notFoundHandler($request, $response);
            }
        );
    }

    /**
    * @return void
    */
    protected function setup_modules()
    {
        $module_manager = $this->module_manager();
        return $module_manager->setup_modules();
    }

    /**
    * ConfigurableTrait > create_config()
    *
    * @param array $data
    * @return AppConfig
    */
    public function create_config(array $data = null)
    {
        return new AppConfig($data);
    }
}
