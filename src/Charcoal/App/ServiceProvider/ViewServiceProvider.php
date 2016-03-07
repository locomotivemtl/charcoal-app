<?php

namespace Charcoal\App\ServiceProvider;

// Pimple dependencies
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\Mustache\MustacheEngine;
use \Charcoal\View\Mustache\MustacheLoader;
use \Charcoal\View\Php\PhpEngine;
use \Charcoal\View\Php\PhpLoader;
use \Charcoal\View\PhpMustache\PhpMustacheEngine;
use \Charcoal\View\Twig\TwigEngine;
use \Charcoal\View\Twig\TwigLoader;
use \Charcoal\View\Renderer;
use \Charcoal\View\ViewConfig;
use \Charcoal\View\ViewInterface;

/**
 * View Service Provider
 *
 * ## Requirements / Dependencies
 * - `config`
 *   - The global / base config (`ConfigInterface`).
 * - `logger`
 *   - A PSR-3 loger.
 *
 * ## Services
 * - `view/config`
 *   - The global view config (`ViewConfig`).
 * - `view`
 *   - The default `ViewInterface` object, determined by `view/config`.
 * - `view/renderer`
 *   - A PSR-7 renderer using the default `view` object.
 *
 * ## Helpers
 * - `view/engine`
 *   - The default `EngineInterface` object, determined by `view/config`.
 */
class ViewServiceProvider implements ServiceProviderInterface
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
         * @return ViewConfig
         */
        $container['view/config'] = function (Container $container) {
            $config = $container['config'];

            $viewConfig =  new ViewConfig($config['view']);
            return $viewConfig;
        };


        $this->registerLoaderServices($container);
        $this->registerEngineServices($container);
        $this->registerViewServices($container);

    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerLoaderServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return array The view loader dependencies array.
         */
        $container['view/loader/dependencies'] = function (Container $container) {
            return [
                'logger'    => $container['logger'],
                'base_path' => $container['config']['ROOT'],
                'paths'     => $container['view/config']['path']
            ];
        };

        /**
         * @param Container $container A container instance.
         * @return MustacheLoader
         */
        $container['view/loader/mustache'] = function (Container $container) {
            return new MustacheLoader($container['view/loader/dependencies']);
        };

        /**
         * @param Container $container A container instance.
         * @return PhpLoader
         */
        $container['view/loader/php'] = function (Container $container) {
            return new PhpLoader($container['view/loader/dependencies']);
        };

        /**
         * @param Container $container A container instance.
         * @return TwigLoader
         */
        $container['view/loader/twig'] = function (Container $container) {
            return new TwigLoader($container['view/loader/dependencies']);
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerEngineServices(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return array The engine dependencies array.
         */
        $container['view/engine/dependencies'] = function (Container $container) {
            return [
                'logger' => $container['logger'],
                'cache'  => null,
                'loader' => null
            ];
        };

        /**
         * @param Container $container A container instance.
         * @return MustacheEngine
         */
        $container['view/engine/mustache'] = function (Container $container) {
            $engineOptions = $container['view/engine/dependencies'];
            $engineOptions['loader'] = $container['view/loader/mustache'];
            return new MustacheEngine($engineOptions);
        };

        /**
         * @param Container $container A container instance.
         * @return PhpEngine
         */
        $container['view/engine/php'] = function (Container $container) {
            $engineOptions = $container['view/engine/dependencies'];
            return new PhpEngine($engineOptions);
        };

        /**
         * @param Container $container A container instance.
         * @return PhpMustacheEngine
         */
        $container['view/engine/php-mustache'] = function (Container $container) {
            $engineOptions = $container['view/engine/dependencies'];
            return new PhpMustacheEngine($engineOptions);
        };

        /**
         * @param Container $container A container instance.
         * @return TwigEngine
         */
        $container['view/engine/twig'] = function (Container $container) {
            $engineOptions = $container['view/engine/dependencies'];
            $engineOptions['loader'] = $container['view/loader/twig'];
            return new TwigEngine($engineOptions);
        };

        /**
         * The default view engine.
         *
         * @param Container $container A container instance.
         * @return \Charcoal\View\EngineInterface
         */
        $container['view/engine'] = function (Container $container) {
            $viewConfig = $container['view/config'];
            $type = $viewConfig['default_engine'];
            return $container['view/engine/'.$type];
        };
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    protected function registerViewServices(Container $container)
    {
        /**
         * The default view instance.
         *
         * @param Container $container A container instance.
         * @return ViewInterface
         */
        $container['view'] = function (Container $container) {
            $viewConfig = $container['view/config'];
            $engine = $container['view/engine'];

            $view = new GenericView([
                'config'=>$viewConfig
            ]);
            $view->setEngine($engine);
            return $view;
        };

        /**
         * @param Container $container A container instance.
         * @return Renderer
         */
        $container['view/renderer'] = function (Container $container) {
            $view = $container['view'];
            $renderer = new Renderer([
                'view'=>$view
            ]);
            return $renderer;
        };
    }
}