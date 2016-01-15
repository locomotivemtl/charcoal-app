<?php

namespace Charcoal\App\Module;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * The ModuleFactory creates Module objects
 */
class ModuleFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\App\Module\ModuleInterface';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Module';
    }
}
