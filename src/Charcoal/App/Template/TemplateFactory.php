<?php

namespace Charcoal\Template;

// Module `charcoal-core` dependencies
use \Charcoal\Core\IdentFactory as IdentFactory;

/**
* The TemplateFactory creates Factory objects
*/
class TemplateFactory extends IdentFactory
{
    /**
    * @param array $data
    */
    public function __construct()
    {
        $this->set_base_class('\Charcoal\Template\TemplateInterface');
    }

    /**
    * IdentFactory > prepare_classname()
    *
    * @param string $class
    * @return string
    */
    public function prepare_classname($class)
    {
        return $class.'Template';
    }
}
