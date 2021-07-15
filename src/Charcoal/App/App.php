<?php

namespace Charcoal\App;

use LogicException;
use RuntimeException;

// From Slim
use Slim\App as SlimApp;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

// From 'charcoal-app'
use Charcoal\App\Route\RouteManager;
use Charcoal\App\Route\RouteFactory;

/**
 * Charcoal App
 *
 * This is the primary class with which you instantiate, configure,
 * and run a Slim Framework application within Charcoal.
 */
class App extends SlimApp implements
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
     * Store the unique instance
     *
     * @var App $instance
     */
    protected static $instance;

    /**
     * @var RouteManager
     */
    private $routeManager;

    /**
     * Getter for creating/returning the unique instance of this class.
     *
     * @param \Pimple\Container|\Slim\Container|array $container The application's settings.
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
     * @param  \Pimple\Container|\Slim\Container|array $container The application's settings.
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

        // Ensure the DI container a proper Slim container.
        // AppContainer is already pre-registered with many usefel services.
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
     * Registers the default services and features that Charcoal needs to work.
     *
     * @return void
     */
    private function setup()
    {
        $config = $this->config();
        date_default_timezone_set($config['timezone']);

        // Setup routes
        $this->routeManager()->setupRoutes();

        // Setup modules
        $this->setupModules();

        // Setup routable (if enabled or not running CLI mode)
        if (PHP_SAPI !== 'cli' && $config['routables'] !== false) {
            $this->setupRoutables();
        }

        // Setup middlewares
        $this->setupMiddlewares();
    }

    /**
     * Retrieve (create, if necessary) the application's route manager.
     *
     * @return RouteManager
     */
    private function routeManager()
    {
        if (!isset($this->routeManager)) {
            $config = $this->config();
            $routesConfig = (isset($config['routes']) ? $config['routes'] : [] );

            $this->routeManager = new RouteManager([
                'config' => $routesConfig,
                'app'    => $this
            ]);
        }

        return $this->routeManager;
    }

    /**
     * @return void
     */
    private function setupModules()
    {
        $container = $this->getContainer();
        $modules = $container['config']['modules'];
        foreach ($modules as $moduleIdent => $moduleConfig) {
            $module = $container['module/factory']->create($moduleIdent);
            $module->setup();
        }
    }

    /**
     * Setup the application's "global" routables.
     *
     * Routables can only be defined globally (app-level) for now.
     *
     * @return void
     */
    private function setupRoutables()
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
                $config    = $app->config();
                $routables = $config['routables'];

                if (is_array($routables) && !empty($routables)) {
                    $routeFactory = $this['route/factory'];
                    foreach ($routables as $routableType => $routableOptions) {
                        $route = $routeFactory->create($routableType, [
                            'path'   => $args['catchall'],
                            'config' => $routableOptions,
                        ]);
                        if ($route->pathResolvable($this)) {
                            $this['logger']->debug(
                                sprintf('Loaded routable "%s" for path %s', $routableType, $args['catchall'])
                            );
                            return $route($this, $request, $response);
                        }
                    }
                }

                // If this point is reached, no routable has provided a callback. 404.
                return $this['notFoundHandler']($request, $response);
            }
        );
    }

    /**
     * @throws RuntimeException If the middleware was not set properly on the container.
     * @return void
     */
    private function setupMiddlewares()
    {
        $container = $this->getContainer();
        $middlewaresConfig = $container['config']['middlewares'];
        if (!$middlewaresConfig) {
            return;
        }

        foreach ($middlewaresConfig as $id => $opts) {
            if (isset($opts['active']) && $opts['active'] === true) {
                if ($id === 'charcoal/app/middleware/cache') {
                    $id = 'charcoal/cache/middleware/cache';

                    $container['logger']->warning(sprintf(
                        'Middleware "%1$s" is deprecated since %3$s. Use "%2$s" instead.',
                        'charcoal/app/middleware/cache',
                        'charcoal/cache/middleware/cache',
                        '0.8.0'
                    ));
                }

                if (!isset($container['middlewares/'.$id])) {
                    throw new RuntimeException(
                        sprintf('Middleware "%s" is not set on container.', $id)
                    );
                }

                $this->add($container['middlewares/'.$id]);
            }
        }
    }

    /**
     * @throws LogicException If trying to clone an instance of a singleton.
     * @return void
     */
    final public function __clone()
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
    final public function __wakeup()
    {
        throw new LogicException(
            sprintf(
                'Unserializing "%s" is not allowed.',
                get_called_class()
            )
        );
    }
}
