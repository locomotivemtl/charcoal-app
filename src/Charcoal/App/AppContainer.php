<?php

namespace Charcoal\App;

// Slim Dependency
use \Slim\Container;

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

        $factory = new Factory([
            'base_class' => '\Pimple\ServiceProviderInterface',
            'resolver_options' => [
                'suffix' => 'ServiceProvider'
            ]
        ]);

        foreach ($providers as $ident => $options) {
            if (false === $options || (isset($options['active']) && !$options['active'])) {
                continue;
            }

            $service = $factory->create($ident);
            $this->register($service);
        }

    }
}
