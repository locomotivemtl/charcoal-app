<?php

namespace Charcoal\App\ServiceProvider;

// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;

// From PSR-3
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

// From Monolog
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-app'
use Charcoal\App\Config\LoggerConfig;

/**
 * Logger Service Provider
 *
 * Provides a Monolog service to a container.
 *
 * ## Services
 *
 * - `logger` `\Psr\Log\Logger`
 *
 * ## Helpers
 *
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
     * @param Container $container A service container.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param  Container $container A service container.
         * @return LoggerConfig
         */
        $container['logger/config'] = function (Container $container) {
            $loggerConfig = ($container['config']['logger'] ?? null);
            return new LoggerConfig($loggerConfig);
        };

        /**
         * @param  Container $container A service container.
         * @return StreamHandler|null
         */
        $container['logger/handler/stream'] = function (Container $container) {
            $loggerConfig  = $container['logger/config'];
            $handlerConfig = $loggerConfig['handlers.stream'];

            if ($handlerConfig['active'] !== true) {
                return null;
            }

            $level = $handlerConfig['level'] ?: $loggerConfig['level'];
            return new StreamHandler($handlerConfig['stream'], $level);
        };

        /**
         * @param  Container $container A service container.
         * @return BrowserConsoleHandler|null
         */
        $container['logger/handler/browser-console'] = function (Container $container) {
            $loggerConfig  = $container['logger/config'];
            $handlerConfig = $loggerConfig['handlers.console'];

            if ($handlerConfig['active'] !== true) {
                return null;
            }

            $level = $handlerConfig['level'] ?: $loggerConfig['level'];
            return new BrowserConsoleHandler($level);
        };

        /**
         * Fulfills the PSR-3 dependency with a Monolog logger.
         *
         * @param  Container $container A service container.
         * @return LoggerInterface
         */
        $container['logger'] = function (Container $container) {
            $loggerConfig = $container['logger/config'];

            if ($loggerConfig['active'] !== true) {
                return new NullLogger();
            }

            $logger = new Logger($loggerConfig['channel']);

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
