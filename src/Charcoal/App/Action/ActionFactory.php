<?php

namespace Charcoal\App\Action;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * The ActionFactory creates Action objects.
 */
class ActionFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\App\Action\ActionInterface';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Action';
    }
}
