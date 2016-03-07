<?php

namespace Charcoal\App\ServiceProvider;

use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependencies from `pimple/pimple`
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\Route\RouteFactory;
use \Charcoal\App\Script\ScriptFactory;
use \Charcoal\App\Template\TemplateFactory;
use \Charcoal\App\Template\TemplateBuilder;
use \Charcoal\App\Template\WidgetFactory;
use \Charcoal\App\Template\WidgetBuilder;

/**
 * Application Service Provider
 *
 * Configures Charcoal and Slim and provides various Charcoal services to a container.
 *
 * ## Services
 * - `logger` `\Psr\Log\Logger`
 *
 * ## Helpers
 * - `logger/config` `\Charcoal\App\Config\LoggerConfig`
 *
 * ## Requirements / Dependencies
 * - `config` A `ConfigInterface` must have been previously registered on the container.
 */
class AppServiceProvider implements ServiceProviderInterface
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
        $this->registerHandlerServices($container);
        $this->registerRouteServices($container);
        $this->registerRequestControllerServices($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerHandlerServices(Container $container)
    {
        if (!isset($container['notFoundHandler'])) {
            /**
             * HTTP 404 (Not found) handler.
             *
             * @param Container $container A container instance.
             * @return callable
             */
            $container['notFoundHandler'] = function (Container $container) {

                return function (RequestInterface $request, ResponseInterface $response) use ($container) {

                    return $container['response']
                        ->withStatus(404)
                        ->withHeader('Content-Type', 'text/html')
                        ->write('Page not found'."\n");
                };
            };
        }

        if (!isset($container['errorHandler'])) {
            /**
             * HTTP 500 (Error) handler.
             *
             * @param Container $container A container instance.
             * @return callable
             */
            $container['errorHandler'] = function (Container $container) {

                return function (
                    RequestInterface $request,
                    ResponseInterface $response,
                    Exception $exception
                ) use ($container) {

                    return $container['response']
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
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerRouteServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return RouteFactory
         */
        $container['route/factory'] = function (Container $container) {
            $routeFactory = new RouteFactory();
            return $routeFactory;
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerRequestControllerServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return ActionFactory
         */
        $container['action/factory'] = function (Container $container) {
            $actionFactory = new ActionFactory();
            return $actionFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return ScriptFactory
         */
        $container['script/factory'] = function (Container $container) {
            $scriptFactory = new ScriptFactory();
            return $scriptFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return TemplateFactory
         */
        $container['template/factory'] = function (Container $container) {
            $templateFactory = new TemplateFactory();
            return $templateFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return TemplateBuilder
         */
        $container['template/builder'] = function (Container $container) {
            $templateBuilder = new TemplateBuilder($container['template/factory'], $container);
            return $templateBuilder;
        };

        /**
         * @param Container $container A container instance.
         * @return WidgetFactory
         */
        $container['widget/factory'] = function (Container $container) {
            $widgetFactory = new WidgetFactory();
            return $widgetFactory;
        };

        /**
         * @param Container $container A container instance.
         * @return TemplateBuilder
         */
        $container['widget/builder'] = function (Container $container) {
            $widgetBuilder = new WidgetBuilder($container['widget/factory'], $container);
            return $widgetBuilder;
        };
    }
}
