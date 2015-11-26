<?php

namespace Charcoal\App\Routable;

// Module `charcoal-core` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * The RoutableFactory creates Routable objects
 */
class RoutableFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function base_class()
    {
        return '\Charcoal\App\Routable\RoutableInterface';
    }
}
