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
         * @return FactoryInterface
         */
        $container['logger/handler/stream'] = function (Container $container) {
            $loggerConfig = $container['logger/config'];
            $handlerConfig = $loggerConfig['handlers.stream'];
            if ($handlerConfig['active'] !== true) {
                return null;
            }

            return new StreamHandler($handlerConfig['stream'], $handlerConfig['level']);
        };

        /**
         * @return FactoryInterface
         */
        $container['logger/handler/browser-console'] = function (Container $container) {
            $loggerConfig = $container['logger/config'];
            $handlerConfig = $loggerConfig['handlers.console'];
            if ($handlerConfig['active'] !== true) {
                return null;
            }

            return new BrowserConsoleHandler($handlerConfig['level']);
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
                $handlers[$h['type']] = function (Container $c) use ($h, $handlerFactory) {
                    $type = $h['type'];
                    $handler = $handlerFactory->create($type);
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

            $logger = new Logger('Charcoal');

            $memProcessor = new MemoryUsageProcessor();
            $logger->pushProcessor($memProcessor);

            $uidProcessor = new UidProcessor();
            $logger->pushProcessor($uidProcessor);

            $consoleHandler = $container['logger/handler/browser-console'];
            if ($consoleHandler) {
                $logger->pushHandler($consoleHandler);
            }

            $streamHandler = $container['logger/handler/stream'];
            if ($streamHandler) {
                $logger->pushHandler($streamHandler);
            }
            return $logger;
        };
    }
}
