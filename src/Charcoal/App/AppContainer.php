<?php

namespace Charcoal\App;

// Slim Dependencies
use \Slim\Container as SlimContainer;

// Monolog Dependencies
use \Monolog\Logger;
use \Monolog\Processor\UidProcessor;
use \Monolog\Handler\StreamHandler;

// Stash Dependencies
use \Stash\Driver\Memcache;
use \Stash\Driver\Ephemeral;
use \Stash\Pool;

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
        $this->register_defaults($config);

        parent::__construct($values);
    }

    /**
     * @param AppConfig $config The Charcoal App configuration.
     * @return void
     */
    private function register_defaults(AppConfig $config)
    {
        $this['charcoal/app/config'] = $config;

        // 404 Not found Handler
        if (!isset($this['notFoundHandler]'])) {
            $this['notFoundHandler'] = function ($c) {

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
            $this['errorHandler'] = function ($c) {

                return function ($request, $response, $exception) use ($c) {


                    return $c['response']
                        ->withStatus(500)
                        ->withHeader('Content-Type', 'text/html')
                        ->write(
                            sprintf('Something went wrong! (%s)'."\n", $exception->getMessage())
                        );
                };
            };
        }

        // PSR-3 logger, with Monolog.
        if (!isset($this['logger'])) {
            $this['logger'] = function ($c) {
                $app_config = $c->get('charcoal/app/config');
                $logger_config = $app_config->get('logger');


                $uid_processor = new UidProcessor();
                $handler = new StreamHandler('charcoal.app.log', Logger::DEBUG);

                $logger = new Logger('Charcoal');
                $logger->pushProcessor($uid_processor);
                $logger->pushHandler($handler);
                return $logger;
            };
        }

        // Stash cache driver
        if (!isset($this['cache/driver'])) {
            $this['cache/driver'] = function ($c) {
                $app_config = $c->get('charcoal/app/config');
                $cache_config = $app_config->get('cache');

                $type = isset($cache_config['type']) ? $cache_config['type'] : 'memcache';
                $prefix = isset($cache_config['prefix']) ? $cache_config['prefix'] : $app_config->get('project_name');

                if ($type == 'memcache') {

                    if (isset($cache_config['servers'])) {
                        $servers = [];
                        foreach ($cache_config['servers'] as $server) {
                            $servers[] = array_values($server);
                        }
                    } else {
                        $servers = [['127.0.0.1', 11211]];
                    }

                    $driver = new Memcache();
                    $driver->setOptions([
                        'servers' => $servers,
                        'prefix_key' => $prefix
                    ]);

                } else {
                    $driver = new Ephemeral();
                }
                return $driver;
            };
        }

        // PSR-6 caching, with Stash
        // (note: stash 0.13 is not yet 100% psr-6 compliant, but as of 2016-01-12 v1.0 is not released)
        if (!isset($this['cache'])) {
            $this['cache'] = function ($c) {
                $driver = $c['cache/driver'];
                $pool = new Pool($driver);
                $pool->setLogger($c['logger']);
                return $pool;
            };

        }
    }
}
