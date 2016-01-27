<?php

namespace Charcoal\App\Provider;

use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Module `charcoal-view` dependencies
use \Charcoal\View\Mustache\MustacheEngine;
use \Charcoal\View\Php\PhpEngine;
use \Charcoal\View\PhpMustache\PhpMustacheEngine;
use \Charcoal\View\Twig\TwigEngine;
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewConfig;
use \Charcoal\View\ViewInterface;

/**
 * View Service Provider
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
        * @param Container $containe A container instance.
        * @return ViewConfig
        */
        $container['view/config'] = function (Container $container) {
            $config = $container['config'];

            $viewConfig =  new ViewConfig($config->get('view'));
            return $viewConfig;
        };

        /**
        * @param Container $containe A container instance.
        * @return array The engine dependencies array.
        */
        $container['view/engine/args'] = function (Container $container) {
            return [
                'logger' => $container['logger'],
                'cache'  => null,
                'loader' => null
            ];
        };
        /**
        * @param Container $containe A container instance.
        * @return MustacheEngine
        */
        $container['view/engine/mustache'] = function (Container $container) {
            return new MustacheEngine($container['view/engine/args']);
        };

        /**
        * @param Container $containe A container instance.
        * @return PhpEngine
        */
        $container['view/engine/php'] = function (Container $container) {
            return new PhpEngine($container['view/engine/args']);
        };

        /**
        * @param Container $containe A container instance.
        * @return PhpMustacheEngine
        */
        $container['view/engine/php-mustache'] = function (Container $container) {
            return new PhpMustacheEngine($container['view/engine/args']);
        };

        /**
        * @param Container $containe A container instance.
        * @return TwigEngine
        */
        $container['view/engine/twig'] = function (Container $container) {
            return new TwigEngine($container['view/engine/args']);
        };

        /**
        * The default view engine.
        *
        * @param Container $containe A container instance.
        * @return \Charcoal\View\EngineInterface
        */
        $container['view/engine'] = function (Container $container) {
            $viewConfig = $container['view/config'];
            $type = $viewConfig['default_engine'];
            return $container['view/engine/'.$type];
        };

        /**
        * The default view instance.
        *
        * @param Container $containe A container instance.
        * @return ViewInterface
        */
        $container['view'] = function (Container $container) {
            $viewConfig = $container['view/config'];
            $engine = $container['view/engine'];

            $view = new \Charcoal\View\GenericView([
                'config'=>$viewConfig
            ]);
            $view->setEngine($engine);
            return $view;
        };

        // PSR7-Renderer object
        $container['view/renderer'] = function (Container $container) {
            $view = $container['view'];
            $renderer = new \Charcoal\View\Renderer([
                'view'=>$view
            ]);
            return $renderer;
        };

    }
}
