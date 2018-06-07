<?php

namespace Charcoal\App\ServiceProvider;

// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;

// From 'league/climate'
use League\CLImate\CLImate;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

use Charcoal\App\Script\ScriptInterface;

/**
 * Script Service Provider
 */
class ScriptServiceProvider implements ServiceProviderInterface
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
        $this->registerScriptFactory($container);
        $this->registerClimate($container);
    }

    /**
     * @param Container $container The DI container.
     * @return void
     */
    private function registerScriptFactory(Container $container)
    {
        /**
         * The Script Factory service is used to instanciate new scripts.
         *
         * - Scripts are `ScriptInterface` and must be suffixed with `Script`.
         * - The container is passed to the created script constructor, which will call `setDependencies()`.
         *
         * @param Container $container A container instance.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['script/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => ScriptInterface::class,
                'resolver_options' => [
                    'suffix' => 'Script'
                ],
                'arguments' => [[
                    'container'      => $container,
                    'logger'         => $container['logger'],
                    'climate'        => $container['script/climate'],
                    'climate_reader' => $container['script/climate/reader']
                ]]
            ]);
        };
    }

    /**
     * @param Container $container A container instance.
     * @return void
     */
    private function registerClimate(Container $container)
    {
        /**
         * @param Container $container A container instance.
         * @return null|\League\CLImate\Util\Reader\ReaderInterface
         */
        $container['script/climate/reader'] = function () {
            return null;
        };

        /**
         * @param Container $container A container instance.
         * @return CLImate
         */
        $container['script/climate'] = function () {
            $climate = new CLImate();
            return $climate;
        };
    }
}
