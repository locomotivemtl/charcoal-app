<?php

namespace Charcoal\App\Provider;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * The ServiceProviderFactory creates ServiceProvider objects
 */
class ServiceProviderFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Pimple\ServiceProviderInterface';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'ServiceProvider';
    }
}
