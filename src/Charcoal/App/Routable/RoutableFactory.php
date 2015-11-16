<?php

namespace Charcoal\App\Routable;

// Module `charcoal-core` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
* The TemplateFactory creates Factory objects
*/
class RoutableFactory extends ResolverFactory
{
    /**
    * @param array $data
    */
    public function base_class()
    {
        return '\Charcoal\App\Routable\RoutableInterface';
    }
}
