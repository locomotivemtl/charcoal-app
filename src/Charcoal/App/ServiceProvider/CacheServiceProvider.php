<?php

namespace Charcoal\App\ServiceProvider;

// Dependencies from `pimple/pimple`
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Dependencies from `tedivm/stash`
use \Stash\DriverList;
use \Stash\Pool;

// Intra-Module `charcoal-app` dependencies
use \Charcoal\App\Config\CacheConfig;

/**
 * Cache Service Provider
 *
 * Provides a Stash cache pool (PSR-6 compatible).
 *
 * ## Dependencies
 * - `config` A base app `\Charcoal\Config\ConfigInterface`
 *
 * ## Services
 * - `cache` A PSR-6 cache `\Stash\Pool
 *
 * ## Helpers
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
         * @param Container $container A container instance.
         * @return CacheConfig
         */
        $container['cache/config'] = function (Container $container) {
            $appConfig = $container['config'];

            $cacheConfig =  new CacheConfig($appConfig->get('cache'));
            return $cacheConfig;
        };

        $container['cache/available-drivers'] = DriverList::getAvailableDrivers();

        /**
         * @param Container $container A container instance.
         * @return Container The Collection of cache drivers, in a Container.
         */
        $container['cache/drivers'] = function (Container $container) {
            $cacheConfig = $container['cache/config'];

            $drivers = new Container();

            $parentContainer = $container;

            /**
             * @param Container $container A container instance.
             * @return \Stash\Driver\Apc
             */
            $drivers['apc'] = function (Container $container) use ($parentContainer) {
                $drivers = $parentContainer['cache/available-drivers'];
                if (!isset($drivers['Apc'])) {
                    // Apc is not available on system
                    return null;
                }
                return new $drivers['Apc']();
            };

            /**
             * @param Container $container A container instance.
             * @return \Stash\Driver\Sqlite
             */
            $drivers['db'] = function (Container $container) use ($parentContainer) {
                $drivers = $parentContainer['cache/available-drivers'];
                if (!isset($drivers['SQLite'])) {
                    // SQLite is not available on system
                    return null;
                }
                return new $drivers['SQLite']();
            };

            /**
             * @param Container $container A container instance.
             * @return \Stash\Driver\FileSystem
             */
            $drivers['file'] = function (Container $container) use ($parentContainer) {
                $drivers = $parentContainer['cache/available-drivers'];
                return new $drivers['FileSystem']();
            };

            /**
             * @param Container $container A container instance.
             * @return \Stash\Driver\Memcache
             */
            $drivers['memcache'] = function (Container $container) use ($parentContainer) {
                $drivers = $parentContainer['cache/available-drivers'];
                if (!isset($drivers['Memcache'])) {
                    // Memcache is not available on system
                    return null;
                }

                $cacheConfig   = $parentContainer['cache/config'];
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
             * @param Container $container A container instance.
             * @return \Stash\Driver\Ephemeral
             */
            $drivers['memory'] = function (Container $container) use ($parentContainer) {
                $drivers = $parentContainer['cache/available-drivers'];
                return new $drivers['Ephemeral']();
            };

            /**
             * @param Container $container A container instance.
             * @return \Stash\Driver\BlackHole
             */
            $drivers['noop'] = function (Container $container) use ($parentContainer) {
                $drivers = $parentContainer['cache/available-drivers'];
                return new $drivers['BlackHole']();
            };

            /**
             * @param Container $container A container instance.
             * @return \Stash\Driver\Redis
             */
            $drivers['redis'] = function (Container $container) use ($parentContainer) {
                $drivers = $parentContainer['cache/available-drivers'];
                if (!isset($drivers['Redis'])) {
                    // Redis is not available on system
                    return null;
                }
                return new $drivers['Redis']();
            };

            return $drivers;
        };

        /**
         * @param Container $container A container instance.
         * @return Container The Collection of DatabaseSourceConfig, in a Container.
         */
        $container['cache/driver'] = function (Container $container) {

            $cacheConfig = $container['cache/config'];
            $types = $cacheConfig->get('types');

            foreach ($types as $type) {
                if (isset($container['cache/drivers'][$type])) {
                    return $container['cache/drivers'][$type];
                }
            }

            // If no working drivers were available, fallback to an Ephemeral (memory) driver.
            return $container['cache/drivers']['memory'];
        };

        /**
         * The cache pool, using Stash.
         *
         * @param Container $container A container instance.
         * @return \Stash\Pool
         */
        $container['cache'] = function (Container $container) {

            $cacheConfig = $container['cache/config'];
            $driver = $container['cache/driver'];

            $pool = new Pool($driver);
            $pool->setLogger($container['logger']);

            // Ensure an alphanumeric namespace (prefix)
            $namespace = preg_replace('/[^A-Za-z0-9 ]/', '', $cacheConfig['prefix']);
            $pool->setNamespace($namespace);

            return $pool;
        };
    }
}
