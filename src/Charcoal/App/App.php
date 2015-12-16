<?php

namespace Charcoal\App;

// PHP Dependencies
use \Exception;
use \LogicException;

// Slim Dependencies
use \Slim\App as SlimApp;

// PSR-7 (HTTP Messaging) Dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Module `charcoal-core` dependencies
use \Charcoal\Log\LoggerAwareInterface;
use \Charcoal\Log\LoggerAwareTrait;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppInterface;

use \Charcoal\App\Language\LanguageManager;
use \Charcoal\App\Middleware\MiddlewareManager;
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;
use \Charcoal\App\Routable\RoutableFactory;

/**
 * Charcoal App
 *
 * This is the primary class with which you instantiate, configure,
 * and run a Slim Framework application within Charcoal.
 */
class App extends SlimApp implements
    AppInterface,
    LoggerAwareInterface,
    ConfigurableInterface
{
    use LoggerAwareTrait;
    use ConfigurableTrait;

    /**
     * Store the unique instance
     *
     * @var App $instance
     */
    protected static $instance;

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
     * Create new Charcoal application (and SlimApp).
     *
     * ### Dependencies
     *
     * **Required**
     *
     * - `charcoal/app/config` — AppConfig
     *
     * **Optional**
     *
     * - `logger` — PSR-3 Logger
     *
     * @uses  SlimApp::__construct()
     * @param ContainerInterface|array $container The application's settings.
     * @throws LogicException If trying to create a new instance of a singleton.
     */
    public function __construct($container)
    {
        if (isset(static::$instance)) {
            throw new LogicException(
                sprintf(
                    '"%s" is a singleton. Use static instance() method.',
                    get_called_class()
                )
            );
        }

        // SlimApp constructor
        parent::__construct($container);

        if (isset($container['charcoal/app/config'])) {
            $this->set_config($container['charcoal/app/config']);
        }
    }

    /**
     * @throws LogicException If trying to clone an instance of a singleton.
     * @return void
     */
    final private function __clone()
    {
        throw new LogicException(
            sprintf(
                'Cloning "%s" is not allowed.',
                get_called_class()
            )
        );
    }

    /**
     * @throws LogicException If trying to unserialize an instance of a singleton.
     * @return void
     */
    final private function __wakeup()
    {
        throw new LogicException(
            sprintf(
                'Unserializing "%s" is not allowed.',
                get_called_class()
            )
        );
    }

    /**
     * Getter for creating/returning the unique instance of this class.
     *
     * @param ContainerInterface|array $container The application's settings.
     * @return self
     */
    public static function instance($container = [])
    {
        if (!isset(static::$instance)) {
            $called_class = get_called_class();

            static::$instance = new $called_class($container);
        }

        return static::$instance;
    }

    /**
     * Retrieve the application's module manager.
     *
     * @return ModuleManager
     */
    public function module_manager()
    {
        if (!isset($this->module_manager)) {
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
     * Retrieve the application's route manager.
     *
     * @return RouteManager
     */
    public function route_manager()
    {
        if (!isset($this->route_manager)) {
            $config = $this->config();
            $routes = (isset($config['routes']) ? $config['routes'] : [] );

            $this->route_manager = new RouteManager([
                'config' => $routes,
                'app'    => $this,
                'logger' => $this->logger()
            ]);
        }

        return $this->route_manager;
    }

    /**
     * Retrieve the application's middleware manager.
     *
     * @return MiddlewareManager
     */
    public function middleware_manager()
    {
        if (!isset($this->middleware_manager)) {
            $config = $this->config();
            $middlewares = (isset($config['middlewares']) ? $config['middlewares'] : [] );

            $this->middleware_manager = new MiddlewareManager([
                'config' => $middlewares,
                'app'    => $this,
                'logger' => $this->logger()
            ]);
        }

        return $this->middleware_manager;
    }

    /**
     * Retrieve the application's language manager.
     *
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

            $this->language_manager = new LanguageManager([
                'config' => $locales,
                'app'    => $this,
                'logger' => $this->logger()
            ]);
        }

        return $this->language_manager;
    }

    /**
     * Registers the default services and features that Charcoal needs to work.
     *
     * @return self
     */
    private function setup()
    {
        $config = $this->config();

        $this->setup_logger();
        $this->setup_languages();
        $this->setup_middlewares();
        $this->setup_routes();
        $this->setup_modules();
        $this->setup_routables();

        date_default_timezone_set($config['timezone']);

        return $this;
    }

    /**
     * Run application
     *
     * Initialize the Charcoal application before running (with SlimApp).
     *
     * @uses   self::setup()
     * @param  boolean $silent If TRUE, will run in silent mode (no response).
     * @return ResponseInterface The PSR7 HTTP response.
     */
    public function run($silent = false)
    {
        $this->setup();

        return parent::run($silent);
    }

    /**
     * Setup the application's logging interface.
     *
     * @return void
     */
    protected function setup_logger()
    {
        $container = $this->getContainer();

        if (isset($container['logger'])) {
            $this->set_logger($container['logger']);
            $this->logger()->debug('Charcoal App Init Logger');
        }
    }

    /**
     * Setup the application's "global" linguistic features, via a LanguageManager
     *
     * @return void
     */
    protected function setup_languages()
    {
        $language_manager = $this->language_manager();
        $language_manager->setup();
    }

    /**
     * Setup the middleware for SlimApp, via a MiddlewareManager
     *
     * @return void
     */
    protected function setup_middlewares()
    {
        $middleware_manager = $this->middleware_manager();
        $middleware_manager->setup_middlewares();
    }

    /**
     * Setup the application's "global" routes, via a RouteManager
     *
     * @return void
     */
    protected function setup_routes()
    {
        $route_manager = $this->route_manager();
        $route_manager->setup_routes();
    }

    /**
     * Setup the application's "global" routables.
     *
     * Routables can only be defined globally (app-level) for now.
     *
     * @return void
     */
    protected function setup_routables()
    {
        $app = $this;
        // For now, need to rely on a catch-all...
        $this->get(
            '{catchall:.*}',
            function (
                RequestInterface $request,
                ResponseInterface $response,
                array $args
            ) use ($app) {
                $c = $app->getContainer();
                $config = $app->config();
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
     * Setup the application's modules, via a ModuleManager
     *
     * @return void
     */
    protected function setup_modules()
    {
        $module_manager = $this->module_manager();
        $module_manager->setup_modules();
    }

    /**
     * Retrieve a new ConfigInterface instance for the object.
     *
     * @see    ConfigurableTrait::create_config() For abstract definition of this method.
     * @param  array|string|null $data Optional configuration data.
     * @return AppConfig
     */
    public function create_config($data = null)
    {
        return new AppConfig($data);
    }
}
