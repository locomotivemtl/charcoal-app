<?php

namespace Charcoal\App\Provider;

// PDO dependencies
use \PDO;

// Dependencies from `pimple/pimple`
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\Config\DatabaseConfig;

/**
 * Database Service Provider. Configures and provides a PDO service to a container.
 *
 * ## Services
 * - `database` The `\PDO` object containeing
 * - `databases` Container of all availables `\PDO` databases.
 *
 * ## Helpers
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
     * @param Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
    {
        /**
        * @param Container $container A container instance.
        * @return Container The Collection of DatabaseSourceConfig, in a Container.
        */
        $container['databases/config'] = function (Container $container) {
            $config = $container['config'];
            $databases = $config->get('databases');
            $configs = new Container();
            foreach ($databases as $dbIdent => $dbOptions) {
                $configs[$dbIdent] = new DatabaseSourceConfig($dbOptions);
            }
            return $configs;
        };

        /**
        * @param Container $container A container instance.
        * @return Container
        */
        $container['databases'] = function (Container $container) {
            $config = $container['config'];
            $databases = $config->get('databases');
            $dbs = new Container();
            foreach ($databases as $dbIdent => $dbOptions) {
                /**
                * @param Container $container A container instance.
                * @return PDO
                */
                $dbs[$dbIdent] = function (Container $container) use ($dbOptions) {
                    $dbConfig = $container['databases/config'][$dbIdent];

                    $type = $dbConfig['type'];
                    $host = $dbConfig['hostname'];

                    $database = $dbConfig['database'];
                    $username = $dbConfig['username'];
                    $password = $dbConfig['password'];

                    // Set UTf-8 compatibility by default. Disable it if it is set as such in config
                    $extra_opts = null;
                    if (!isset($dbConfig['disable_utf8']) || !$dbConfig['disable_utf8']) {
                        $extra_opts = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
                    }

                    $dsn = $db_type.':host='.$db_hostname.';dbname='.$database;
                    $db = new PDO($dsn, $username, $password, $extra_opts);

                    // Set PDO options
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if ($db_type == 'mysql') {
                        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                    }
                    return $db;
                };
            }
            return $dbs;
        };

        /**
        * @param Container $container A container instance.
        * @return DatabaseSourceConfig
        */
        $container['database/config'] = function (Container $container) {
            $config = $container['config'];
            $databaseIdent = $config->get('default_database');
            return $container['databases/config'][$databaseIdent];
        };

        /**
        * The database service, as a PDO object.
        *
        * @param Container $container A container instance.
        * @return PDO
        */
        $container['database'] = function (Container $container) {
            $config = $container['config'];
            $databaseIdent = $config->get('default_database');
            return $container['databases'][$databaseIdent];
        };
    }
}
