<?php

namespace Charcoal\App;

// PHP Dependencies
use \Exception;
use \LogicException;

// Slim Dependencies
use \Slim\App as SlimApp;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// PSR-7 (HTTP Messaging) Dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

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
    private $moduleManager;

    /**
     * @var RouteManager
     */
    private $routeManager;

    /**
     * @var MiddlewareManager
     */
    private $middlewareManager;

    /**
     * @var LanguageManager
     */
    private $languageManager;

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
            $this->setConfig($container['charcoal/app/config']);
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
    public function moduleManager()
    {
        if (!isset($this->moduleManager)) {
            $config  = $this->config();
            $modules = (isset($config['modules']) ? $config['modules'] : [] );

            $this->moduleManager = new ModuleManager([
                'config' => $modules,
                'app'    => $this,
                'logger' => $this->logger
            ]);
        }

        return $this->moduleManager;
    }

    /**
     * Retrieve the application's route manager.
     *
     * @return RouteManager
     */
    public function routeManager()
    {
        if (!isset($this->routeManager)) {
            $config = $this->config();
            $routes = (isset($config['routes']) ? $config['routes'] : [] );

            $this->routeManager = new RouteManager([
                'config' => $routes,
                'app'    => $this,
                'logger' => $this->logger
            ]);
        }

        return $this->routeManager;
    }

    /**
     * Retrieve the application's middleware manager.
     *
     * @return MiddlewareManager
     */
    public function middlewareManager()
    {
        if (!isset($this->middlewareManager)) {
            $config = $this->config();
            $middlewares = (isset($config['middlewares']) ? $config['middlewares'] : [] );

            $this->middlewareManager = new MiddlewareManager([
                'config' => $middlewares,
                'app'    => $this,
                'logger' => $this->logger
            ]);
        }

        return $this->middlewareManager;
    }

    /**
     * Retrieve the application's language manager.
     *
     * @return LanguageManager
     */
    public function languageManager()
    {
        if (!isset($this->languageManager)) {
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

            $this->languageManager = new LanguageManager([
                'config' => $locales,
                'app'    => $this,
                'logger' => $this->logger
            ]);
        }

        return $this->languageManager;
    }

    /**
     * Registers the default services and features that Charcoal needs to work.
     *
     * @return self
     */
    private function setup()
    {
        $config = $this->config();

        $this->setupLogger();
        $this->setupLanguages();
        $this->setupMiddlewares();
        $this->setupRoutes();
        $this->setupModules();
        $this->setupRoutables();

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
    protected function setupLogger()
    {
        $container = $this->getContainer();

        if (isset($container['logger'])) {
            $this->setLogger($container['logger']);
            $this->logger->debug('Charcoal App Init Logger');
        }
    }

    /**
     * Setup the application's "global" linguistic features, via a LanguageManager
     *
     * @return void
     */
    protected function setupLanguages()
    {
        $languageManager = $this->languageManager();
        $languageManager->setup();
    }

    /**
     * Setup the middleware for SlimApp, via a MiddlewareManager
     *
     * @return void
     */
    protected function setupMiddlewares()
    {
        $middlewareManager = $this->middlewareManager();
        $middlewareManager->setupMiddlewares();
    }

    /**
     * Setup the application's "global" routes, via a RouteManager
     *
     * @return void
     */
    protected function setupRoutes()
    {
        $routeManager = $this->routeManager();
        $routeManager->setupRoutes();
    }

    /**
     * Setup the application's "global" routables.
     *
     * Routables can only be defined globally (app-level) for now.
     *
     * @return void
     */
    protected function setupRoutables()
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
    protected function setupModules()
    {
        $moduleManager = $this->moduleManager();
        $moduleManager->setupModules();
    }

    /**
     * Retrieve a new ConfigInterface instance for the object.
     *
     * @see    ConfigurableTrait::create_config() For abstract definition of this method.
     * @param  array|string|null $data Optional configuration data.
     * @return AppConfig
     */
    public function createConfig($data = null)
    {
        return new AppConfig($data);
    }
}
