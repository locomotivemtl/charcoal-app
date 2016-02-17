<?php

namespace Charcoal\App\ServiceProvider;

// Dependencies from `pimple/pimple`
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// PSR-3 (log) dependencies
use \Psr\Log\NullLogger;

// Monolog Dependencies
use \Monolog\Logger;
use \Monolog\Processor\MemoryUsageProcessor;
use \Monolog\Processor\UidProcessor;
use \Monolog\Handler\BrowserConsoleHandler;
use \Monolog\Handler\StreamHandler;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\MapFactory;

// Intra-Module `charcoal-app` dependencies
use \Charcoal\App\Config\LoggerConfig;

/**
 * Logger Service Provider
 *
 * Provides a Monolog service to a container.
 *
 * ## Services
 * - `logger` `\Psr\Log\Logger`
 *
 * ## Helpers
 * - `logger/config` `\Charcoal\App\Config\LoggerConfig`
 */
class LoggerServiceProvider implements ServiceProviderInterface
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
         * @return LoggerConfig
         */
        $container['logger/config'] = function (Container $container) {
            $config = $container['config'];

            $loggerConfig = new LoggerConfig($config->get('logger'));
            return $loggerConfig;
        };

        /**
         * @return MapFactory
         */
        $container['logger/processor/factory'] = function (Container $contianer) {
            $mapFactory = new MapFactory();
            $mapFactory->setMap([
                'memory-usage'  => '\Monolog\Processor\MemoryUsageProcessor',
                'uid'           => '\Monolog\Processor\UidProcessor'
            ]);
            return $mapFactory;
        };

        /**
         * @return MapFactory
         */
        $container['logger/handler/factory'] = function (Container $contianer) {
            $mapFactory = new MapFactory();
            $mapFactory->setBaseClass('\Monolog\Handler\HandlerInterface');
            $mapFactory->setMap([
                'stream'            => '\Monolog\Handler\StreamHandler',
                'browser-console'   => '\Monolog\Handler\BrowserConsoleHandler'
            ]);
            return $mapFactory;
        };

        /**
         * @return Container
         */
        $container['logger/handlers'] = function (Container $container) {
            $loggerConfig = $container['logger/config'];

            $handlersConfig = $loggerConfig['handlers'];
            $handlers = new Container();
            $handlerFactory = $container['logger/handler/factory'];
            foreach ($handlersConfig as $h) {
                $type = $h['type'];
                $handlers[$type] = function (Container $c) use ($h) {
                    $args = [];
                    $handler = $handlerFactory->create($type, $args);
                    return $handler;
                };
            }
            return $handlers;
        };

        /**
         * Fulfills the PSR-3 dependency with a Monolog logger.
         *
         * @param Container $container A container instance.
         * @return \Psr\Log\Logger
         */
        $container['logger'] = function (Container $container) {

            $loggerConfig = $container['logger/config'];

            if ($loggerConfig['active'] !== true) {
                return new NullLogger();
            }


            $memProcessor = new MemoryUsageProcessor();
            $uidProcessor = new UidProcessor();

            $browserHandler = new BrowserConsoleHandler(Logger::DEBUG);
            $streamHandler = new StreamHandler('charcoal.app.log', Logger::DEBUG);

            $logger = new Logger('Charcoal');
            $logger->pushProcessor($memProcessor);
            $logger->pushProcessor($uidProcessor);
            $logger->pushHandler($browserHandler);
            $logger->pushHandler($streamHandler);
            return $logger;
        };
    }
}
