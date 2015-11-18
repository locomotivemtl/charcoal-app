<?php

namespace Charcoal\App\Module;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
* The TemplateFactory creates Template objects
*/
class ModuleFactory extends ResolverFactory
{
    /**
    * @param array $data
    */
    public function base_class()
    {
        return '\Charcoal\App\Module\ModuleInterface';
    }

    /**
    * @return string
    */
    public function resolver_suffix()
    {
        return 'Module';
    }
}
