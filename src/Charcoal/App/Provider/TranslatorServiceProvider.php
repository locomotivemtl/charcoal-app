<?php

namespace Charcoal\App\Provider;

// Dependencies from `pimple/pimple`
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Intra-Module `charcoal-app` dependencies
use \Charcoal\App\Config\LoggerConfig;

/**
 * Translator Service Provider. Configures and providers a Symfony Translator service to a container.
 *
 * ## Services
 * - `translator`
 *
 * ## Helpers
 * - `translator/config` `\Charcoal\A[p\Config\LoggerConfig`
 *
 * ## Requirements / Dependencies
 * - `config` A `ConfigInterface` must have been previously registered on the container.
 */
class TranslatorServiceProvider implements ServiceProviderInterface
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
        $container['translator/config'] = function (Container $container) {
            $appConfig = $container['config'];
            $translatorConfig = new \Charcoal\App\Config\TranslatorConfig($appConfig->get('translator'));
            return $translatorConfig;
        };

        $container['translator'] = function (Container $container) {
                return [];
        };
    }
}
