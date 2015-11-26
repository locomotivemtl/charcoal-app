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
    public function base_class()
    {
        return '\Charcoal\App\Template\TemplateInterface';
    }

    /**
     * @return string
     */
    public function resolver_suffix()
    {
        return 'Template';
    }
}
