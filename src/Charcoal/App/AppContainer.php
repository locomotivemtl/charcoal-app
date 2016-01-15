<?php

namespace Charcoal\App;

// Slim Dependencies
use \Slim\Container as SlimContainer;

// Monolog Dependencies
use \Monolog\Logger;
use \Monolog\Processor\UidProcessor;
use \Monolog\Handler\StreamHandler;

// Stash Dependencies
use \Stash\DriverList;
use \Stash\Driver\Ephemeral;
use \Stash\Pool;

use \Charcoal\App\Config\LoggerConfig;
use \Charcoal\App\Config\CacheConfig;
use \Charcoal\App\Config\MemcacheCacheConfig;

/**
 * Charcoal App Container
 */
class AppContainer extends SlimContainer
{
    /**
     * Create new container
     *
     * @param array $values The parameters or objects.
     */
    public function __construct(array $values = [])
    {
        $config = isset($values['config']) ? $values['config'] : [];
        $this->registerDefaults($config);

        parent::__construct($values);
    }

    /**
     * @param AppConfig $config The Charcoal App configuration.
     * @return void
     */
    private function registerDefaults(AppConfig $config)
    {
        $this['charcoal/app/config'] = $config;

        $this->registerHandlers();
        $this->registerLogger();
        $this->registerCache();
    }

    /**
     * @return void
     */
    private function registerHandlers()
    {
        // 404 Not found Handler
        if (!isset($this['notFoundHandler]'])) {
            $this['notFoundHandler'] = function (AppContainer $c) {

                return function ($request, $response) use ($c) {

                    return $c['response']
                        ->withStatus(404)
                        ->withHeader('Content-Type', 'text/html')
                        ->write('Page not found'."\n");
                };
            };
        }

        // 500 Error Handler
        if (!isset($this['errorHandler'])) {
            $this['errorHandler'] = function (AppContainer $c) {

                return function ($request, $response, $exception) use ($c) {

                    $c['logger']->critical('500 Error', (array)$exception);

                    return $c['response']
                        ->withStatus(500)
                        ->withHeader('Content-Type', 'text/html')
                        ->write(
                            sprintf('Something went wrong! (%s)'."\n", $exception->getMessage())
                        );
                };
            };
        }
    }

    /**
     * @return void
     */
    private function registerLogger()
    {
        // LoggerConfig object
        if (!isset($this['logger/config'])) {
            $this['logger/config'] = function (AppContainer $c) {
                $app_config = $c->get('charcoal/app/config');

                $logger_config = new LoggerConfig($app_config->get('logger'));
                return $logger_config;
            };
        }

        // PSR-3 logger, with Monolog.
        if (!isset($this['logger'])) {
            $this['logger'] = function (AppContainer $c) {

                $app_config = $c->get('charcoal/app/config');

                $logger_config = $c->get('logger/config');

                $uid_processor = new UidProcessor();
                $handler = new StreamHandler('charcoal.app.log', Logger::DEBUG);

                $logger = new Logger('Charcoal');
                $logger->pushProcessor($uid_processor);
                $logger->pushHandler($handler);
                return $logger;
            };
        }

    }

    /**
     * Set up required caching system dependencies, if it was not previously set.
     *
     * @return void
     */
    private function registerCache()
    {
                // CacheConfig object
        if (!isset($this['cache/config'])) {
            $this['cache/config'] = function (AppContainer $c) {
                $app_config = $c->get('charcoal/app/config');

                $cache_config =  new CacheConfig($app_config->get('cache'));
                return $cache_config;
            };
        }

        // Stash cache driver
        if (!isset($this['cache/driver'])) {
            $this['cache/driver'] = function (AppContainer $c) {

                $cache_config = $c->get('cache/config');

                $types = $cache_config->get('types');

                $stash_types = [
                    'apc'       => 'Apc',
                    'file'      => 'FileSystem',
                    'db'        => 'SQLite',
                    'memcache'  => 'Memcache',
                    'memory'    => 'Ephemeral',
                    'noop'      => 'BlackHole',
                    'redis'     => 'Redis'
                ];

                $available_drivers = \Stash\DriverList::getAvailableDrivers();
                foreach ($types as $type) {
                    $stash_type = $stash_types[$type];
                    if (!isset($available_drivers[$stash_type])) {
                        continue;
                    }
                    $class = $available_drivers[$stash_type];
                    $driver = new $class();
                    if ($type == 'memcache') {
                        if (isset($cache_config['servers'])) {
                            $servers = [];
                            foreach ($cache_config['servers'] as $server) {
                                $servers[] = array_values($server);
                            }
                        } else {
                            $servers = [['127.0.0.1', 11211]];
                        }
                        $driver->setOptions([
                            'servers'=>$servers
                        ]);
                    }
                    break;
                }

                // If no working drivers were available, fallback to an Ephemeral (memory) driver.
                if (!isset($driver)) {
                    $driver = new Ephemeral();
                }


                return $driver;
            };
        }

        // PSR-6 caching, with Stash
        // (note: stash 0.13 is not yet 100% psr-6 compliant, but as of 2016-01-12 v1.0 is not released)
        if (!isset($this['cache'])) {
            $this['cache'] = function (AppContainer $c) {

                $cache_config = $c->get('cache/config');
                $driver = $c->get('cache/driver');

                $pool = new Pool($driver);
                $pool->setLogger($c->get('logger'));
                $pool->setNamespace($cache_config['prefix']);

                return $pool;
            };

        }
    }
}
