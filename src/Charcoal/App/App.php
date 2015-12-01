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
class App extends SlimApp implements
    AppInterface,
    SingletonInterface,
    ConfigurableInterface
{
    use SingletonTrait;
    use LoggerAwareTrait;
    use ConfigurableTrait;

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
     * - `container` A
     *
     * @param mixed $container Dependencies.
     */
    public function __construct($container)
    {
        // Slim constructor
        parent::__construct($container);

        $this->set_config($container['charcoal/app/config']);

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
                'app'    => $this,
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
                'app'    => $this,
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
                'app'    => $this,
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
                'app'    => $this,
                'logger' => $this->logger()
            ]);
        }
        return $language_manager;
    }

    /**
     * Initialize the Charcoal App before running (with SlimApp).
     *
     * @param boolean $silent If true, will run in silent mot (no response).
     * @return App Chainable
     */
    public function run($silent = false)
    {
        $this->setup_languages();
        $this->setup_middlewares();
        $this->setup_routes();
        $this->setup_modules();
        $this->setup_routables();

        return parent::run($silent);
    }

    /**
     * @return void
     */
    protected function setup_languages()
    {
        $language_manager = $this->language_manager();
        $language_manager->setup();
    }

    /**
     * @return void
     */
    protected function setup_middlewares()
    {
        $middleware_manager = $this->middleware_manager();
        $middleware_manager->setup_middlewares();
    }

    /**
     * Set up the app's "global" routes, via a RouteManager
     *
     * @return void
     */
    protected function setup_routes()
    {
        $route_manager = $this->route_manager();
        $route_manager->setup_routes();
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
        $this->get(
            '{catchall:.*}',
            function (
                RequestInterface $request,
                ResponseInterface $response,
                array $args
            ) use ($charcoal) {
                $c = $this->getContainer();
                $config = $charcoal->config();
                $routables = $config['routables'];
                if ($routables === null || count($routables) === 0) {
                    return $c['notFoundHandler']($request, $response);
                }
                $routable_factory = new RoutableFactory();
                foreach ($routables as $routable_type => $routable_options) {
                    $routable = $routable_factory->create($routable_type);
                    $route = $routable->route_handler($args['catchall'], $request, $response);
                    if ($route) {
                        return $route($request, $response);
                    }
                }

    
                // If this point is reached, no routable has provided a callback. 404.
                return $c['notFoundHandler']($request, $response);
            }
        );
    }

    /**
     * @return void
     */
    protected function setup_modules()
    {
        $module_manager = $this->module_manager();
        $module_manager->setup_modules();
    }

    /**
     * ConfigurableTrait > create_config()
     *
     * @param array $data Optional config data.
     * @return AppConfig
     */
    public function create_config(array $data = null)
    {
        return new AppConfig($data);
    }
}
