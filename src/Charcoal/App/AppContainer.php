<?php

namespace Charcoal\App;

// Slim Dependency
use \Slim\Container;

// Intra-Module `charcoal-app` dependencies
use \Charcoal\App\Provider\ServiceProviderFactory;

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

        $this['config'] = isset($values['config']) ? $values['config'] : [];

        $defaults = [
            'charcoal/app/provider/app'        => [],
            'charcoal/app/provider/cache'      => [],
            'charcoal/app/provider/database'   => [],
            'charcoal/app/provider/logger'     => [],
            'charcoal/app/provider/translator' => [],
            'charcoal/app/provider/view'       => [],
        ];

        $providers = $this['config']->get('service_providers');
        $factory   = new ServiceProviderFactory();

        if (is_array($providers) && count($providers)) {
            $providers = array_replace($defaults, $providers);
        } else {
            $providers = $defaults;
        }

        foreach ($providers as $ident => $options) {
            if (false === $options || (isset($options['active']) && !$options['active'])) {
                continue;
            }

            $service = $factory->create($ident);
            $this->register($service);
        }

    }
}
