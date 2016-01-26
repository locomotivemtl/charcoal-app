<?php

namespace Charcoal\App\Template;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * The TemplateFactory creates Template objects
 */
class TemplateFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\App\Template\TemplateInterface';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Template';
    }
}