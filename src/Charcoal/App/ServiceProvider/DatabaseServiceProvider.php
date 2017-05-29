<?php

namespace Charcoal\App\ServiceProvider;

use Exception;
use PDO;

// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;

// From PSR-11
use Psr\Container\ContainerInterface;

// From 'charcoal-app'
use Charcoal\App\Config\DatabaseConfig;

/**
 * Database Service Provider. Configures and provides a PDO service to a container.
 *
 * ## Services
 *
 * - `database` The `\PDO` instance.
 * - `databases` Container of all availables `\PDO` databases.
 *
 * ## Helpers
 *
 * - `database/config` A `DatabaseConfig` object containing the DB settings.
 * - `databases/config A container of `DatabaseConfig`
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param  ContainerInterface $container A container instance.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        /**
         * @param  ContainerInterface $container A container instance.
         * @return Container The collection of DatabaseSourceConfig, in a Container.
         */
        $container['databases/config'] = function (ContainerInterface $container) {
            $config = $container['config'];
            $databases = $config['databases'];
            $configs = new Container();
            foreach ($databases as $dbIdent => $dbOptions) {
                $configs[$dbIdent] = new DatabaseConfig($dbOptions);
            }
            return $configs;
        };

        /**
         * @param  ContainerInterface $container A container instance.
         * @return Container
         */
        $container['databases'] = function (ContainerInterface $container) {
            $config = $container['config'];
            $databases = $config['databases'];
            $dbs = new Container();
            $origContainer = $container;
            foreach ($databases as $dbIdent => $dbOptions) {
                unset($dbOptions);

                /**
                 * @param  ContainerInterface $container A container instance.
                 * @return PDO
                 */
                $dbs[$dbIdent] = function () use ($dbIdent, $origContainer) {
                    $dbConfigs = $origContainer['databases/config'];
                    $dbConfig = $dbConfigs[$dbIdent];

                    $type = $dbConfig['type'];
                    $host = $dbConfig['hostname'];

                    $database = $dbConfig['database'];
                    $username = $dbConfig['username'];
                    $password = $dbConfig['password'];

                    // Set UTf-8 compatibility by default. Disable it if it is set as such in config
                    $extraOptions = null;
                    if (!isset($dbConfig['disable_utf8']) || !$dbConfig['disable_utf8']) {
                        $extraOptions = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'];
                    }

                    if ($type == 'sqlite') {
                        $dsn = $type.':'.$database;
                    } else {
                        $dsn = $type.':host='.$host.';dbname='.$database;
                    }
                    $db = new PDO($dsn, $username, $password, $extraOptions);

                    // Set PDO options
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if ($type == 'mysql') {
                        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                    }
                    return $db;
                };
            }
            return $dbs;
        };

        /**
         * @param  ContainerInterface $container A container instance.
         * @return DatabaseSourceConfig
         */
        $container['database/config'] = function (ContainerInterface $container) {
            $config = $container['config'];
            $databaseIdent = $config['default_database'];
            return $container['databases/config'][$databaseIdent];
        };

        /**
         * The database service, as a PDO object.
         *
         * @param  ContainerInterface $container A container instance.
         * @throws Exception If the database configuration is invalid.
         * @return PDO
         */
        $container['database'] = function (ContainerInterface $container) {
            $config = $container['config'];
            $databaseIdent = $config['default_database'];
            $databases = $container['databases'];
            if (!isset($databases[$databaseIdent])) {
                throw new Exception(
                    sprintf('The database "%s" is not defined in the "databases" configuration.', $databaseIdent)
                );
            }
            return $databases[$databaseIdent];
        };
    }
}
