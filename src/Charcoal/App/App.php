<?php

namespace Charcoal\App;

// PHP Dependencies
use \Exception;
use \LogicException;

// Dependency from 'Slim'
use \Slim\App as SlimApp;

// Dependencies from 'PSR-3' (Logging)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Dependencies from 'PSR-7' (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependencies from 'charcoal-config'
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;
use \Charcoal\App\Module\ModuleManager;
use \Charcoal\App\Route\RouteManager;
use \Charcoal\App\Route\RouteFactory;

/**
 * Charcoal App
 *
 * This is the primary class with which you instantiate, configure,
 * and run a Slim Framework application within Charcoal.
 */
class App extends SlimApp implements
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
     * Create new Charcoal application (and SlimApp).
     *
     * ### Dependencies
     *
     * **Required**
     *
     * - `config` — AppConfig
     *
     * **Optional**
     *
     * - `logger` — PSR-3 Logger
     *
     * @uses  SlimApp::__construct()
     * @param ContainerInterface|array $container The application's settings.
     * @throws LogicException If trying to create a new instance of a singleton.
     */
    public function __construct($container = [])
    {
        if (isset(static::$instance)) {
            throw new LogicException(
                sprintf(
                    '"%s" is a singleton. Use static instance() method.',
                    get_called_class()
                )
            );
        }

        // Ensure the DI container is
        if (is_array($container)) {
            $container = new AppContainer($container);
        }

        // SlimApp constructor
        parent::__construct($container);

        if (isset($container['config'])) {
            $this->setConfig($container['config']);
        }
    }

    /**
     * Run application.
     *
     * Initialize the Charcoal application before running (with SlimApp).
     *
     * @uses   self::setup()
     * @param  boolean $silent If true, will run in silent mode (no response).
     * @return ResponseInterface The PSR7 HTTP response.
     */
    public function run($silent = false)
    {
        $this->setup();

        return parent::run($silent);
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
     * Retrieve the application's module manager.
     *
     * @return ModuleManager
     */
    protected function moduleManager()
    {
        if (!isset($this->moduleManager)) {
            $config  = $this->config();
            $modules = (isset($config['modules']) ? $config['modules'] : [] );

            $container = $this->getContainer();
            $this->moduleManager = new ModuleManager([
                'config' => $modules,
                'app'    => $this,
                'logger' => $this->logger,
                'module_factory' => $container['module/factory']
            ]);
        }

        return $this->moduleManager;
    }

    /**
     * Retrieve the application's route manager.
     *
     * @return RouteManager
     */
    protected function routeManager()
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
     * Registers the default services and features that Charcoal needs to work.
     *
     * @return AppInterface Chainable
     */
    private function setup()
    {
        $config = $this->config();
        date_default_timezone_set($config['timezone']);

        $this->setupLogger();
        $this->setupRoutes();
        $this->setupModules();
        $this->setupRoutables();

        return $this;
    }

    /**
     * Setup the application's logging interface.
     *
     * @return void
     */
    protected function setupLogger()
    {
        $container = $this->getContainer();

        if (!isset($container['logger'])) {
            $container['logger'] = new \Psr\Log\NullLogger();
        }

        $this->setLogger($container['logger']);
        $this->logger->debug('>>> Charcoal App Init Logger');

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
                $config = $app->config();
                $routables = $config['routables'];
                if ($routables === null || count($routables) === 0) {
                    return $this['notFoundHandler']($request, $response);
                }
                $routeFactory = $this['route/factory'];
                foreach ($routables as $routableType => $routableOptions) {
                    $route = $routeFactory->create($routableType, [
                        'path' => $args['catchall'],
                        'config' => $routableOptions
                    ]);
                    if ($route->pathResolvable($this)) {
                        $this['logger']->debug(
                            sprintf('Loaded routable "%s" for path %s', $routableType, $args['catchall'])
                        );
                        return $route($this, $request, $response);
                    }
                }

                // If this point is reached, no routable has provided a callback. 404.
                return $this['notFoundHandler']($request, $response);
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
     * @see    ConfigurableTrait::createConfig() For abstract definition of this method.
     * @param  array|string|null $data Optional configuration data.
     * @return AppConfig
     */
    protected function createConfig($data = null)
    {
        return new AppConfig($data);
    }
}
