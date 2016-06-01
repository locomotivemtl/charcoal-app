<?php

namespace Charcoal\App;

// Dependency from Slim
use \Slim\Container;

// Dependency from Pimple
use \Pimple\ServiceProviderInterface;

// Depedencies from `charcoal-factory`
use \Charcoal\Factory\GenericFactory as Factory;

/**
 * Charcoal App Container
 */
class AppContainer extends Container
{
    /**
     * Create new container
     *
     * @param array $values The parameters or objects.
     */
    public function __construct(array $values = [])
    {
        // Initialize container for Slim and Pimple
        parent::__construct($values);

        $this['config'] = (isset($values['config']) ? $values['config'] : []);

        if (!isset($container['provider/factory'])) {
            $this['provider/factory'] = function (Container $container) {
                return new Factory([
                    'base_class'       => ServiceProviderInterface::class,
                    'resolver_options' => [
                        'suffix' => 'ServiceProvider'
                    ]
                ]);
            };
        }

        $defaults = [
            'charcoal/app/service-provider/app'        => [],
            'charcoal/app/service-provider/cache'      => [],
            'charcoal/app/service-provider/database'   => [],
            'charcoal/app/service-provider/logger'     => [],
            'charcoal/app/service-provider/translator' => [],
            'charcoal/app/service-provider/view'       => [],
        ];

        if (!empty($this['config']['service_providers'])) {
            $providers = array_replace($defaults, $this['config']['service_providers']);
        } else {
            $providers = $defaults;
        }


        foreach ($providers as $provider => $values) {
            if (false === $values) {
                continue;
            }

            if (!is_array($values)) {
                $values = [];
            }

            $provider = $this['provider/factory']->get($provider);

            $this->register($provider, $values);
        }
    }
}
