<?php

namespace Charcoal\App\Route;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * The RouteFactory creates Route (Request Controller) objects.
 */
class RouteFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\App\Route\RouteInterface';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Route';
    }
}
