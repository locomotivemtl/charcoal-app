<?php

namespace Charcoal\App\ServiceProvider;

// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;

// From 'tedivm/stash'
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

// From 'charcoal-app'
use Charcoal\App\Config\CacheConfig;
use Charcoal\App\Middleware\CacheMiddleware;

/**
 * Cache Service Provider
 *
 * Provides a Stash cache pool (PSR-6 compatible).
 *
 * ## Dependencies
 *
 * - `config` A base app `\Charcoal\Config\ConfigInterface`
 *
 * ## Services
 *
 * - `cache` A PSR-6 cache `\Stash\Pool
 *
 * ## Helpers
 *
 * - `cache/config` The cache config `\Charcoal\App\Config\CacheConfig`
 * - `cache/driver` The default cache driver`Stash\Interfaces\DriverInterface`
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param  Container $container The service container.
         * @return CacheConfig
         */
        $container['cache/config'] = function (Container $container) {
            $appConfig   = isset($container['config']) ? $container['config'] : [];
            $cacheConfig = isset($appConfig['cache']) ? $appConfig['cache'] : null;
            return new CacheConfig($cacheConfig);
        };

        $container['cache/available-drivers'] = DriverList::getAvailableDrivers();

        /**
         * @param  Container $container The service container.
         * @return Container Collection of cache drivers, in a service container.
         */
        $container['cache/drivers'] = function (Container $container) {
            $drivers = new Container();

            /**
             * @param  Container $container The service container.
             * @return \Stash\Driver\Apc|null
             */
            $drivers['apc'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['Apc'])) {
                    // Apc is not available on system
                    return null;
                }
                return new $drivers['Apc']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Sqlite|null
             */
            $drivers['db'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['SQLite'])) {
                    // SQLite is not available on system
                    return null;
                }
                return new $drivers['SQLite']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\FileSystem
             */
            $drivers['file'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                return new $drivers['FileSystem']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Memcache|null
             */
            $drivers['memcache'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['Memcache'])) {
                    // Memcache is not available on system
                    return null;
                }

                $cacheConfig   = $container['cache/config'];
                $driverOptions = [
                    'servers' => []
                ];

                if (isset($cacheConfig['servers'])) {
                    $servers = [];
                    foreach ($cacheConfig['servers'] as $server) {
                        $servers[] = array_values($server);
                    }
                    $driverOptions['servers'] = $servers;
                } else {
                    // Default Memcache options: locahost:11211
                    $driverOptions['servers'][] = [ '127.0.0.1', 11211 ];
                }

                return new $drivers['Memcache']($driverOptions);
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Ephemeral
             */
            $drivers['memory'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                return new $drivers['Ephemeral']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\BlackHole
             */
            $drivers['noop'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                return new $drivers['BlackHole']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Redis|null
             */
            $drivers['redis'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['Redis'])) {
                    // Redis is not available on system
                    return null;
                }
                return new $drivers['Redis']();
            };

            return $drivers;
        };

        /**
         * @param  Container $container The service container.
         * @return DriverInterface Primary cache driver.
         */
        $container['cache/driver'] = function (Container $container) {
            $cacheConfig = $container['cache/config'];

            if ($cacheConfig['active'] === true) {
                $cacheTypes = $cacheConfig['types'];
                foreach ($cacheTypes as $type) {
                    if (isset($container['cache/drivers'][$type])) {
                        return $container['cache/drivers'][$type];
                    }
                }
            }

            /**
             * If no working drivers were available
             * or the cache is disabled,
             * use the memory driver.
             */
            return $container['cache/drivers']['memory'];
        };

        /**
         * The cache pool, using Stash.
         *
         * @param  Container $container The service container.
         * @return Pool
         */
        $container['cache'] = function (Container $container) {
            $cacheConfig = $container['cache/config'];
            $cacheDriver = $container['cache/driver'];

            $pool = new Pool($cacheDriver);
            $pool->setLogger($container['logger']);

            // Ensure an alphanumeric namespace (prefix)
            $namespace = preg_replace('/[^A-Za-z0-9 ]/', '', $cacheConfig['prefix']);
            $pool->setNamespace($namespace);

            return $pool;
        };
    }
}
