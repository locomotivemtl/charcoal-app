<?php

namespace Charcoal\Template;

// Module `charcoal-core` dependencies
use \Charcoal\Core\IdentFactory as IdentFactory;

/**
* The WidgetFactory creates Widget objects.
*/
class WidgetFactory extends IdentFactory
{
    /**
    * @param array $data
    */
    public function __construct()
    {
        $this->set_base_class('\Charcoal\Widget\WidgetInterface');
    }

    /**
    * IdentFactory > prepare_classname()
    *
    * Widgets class names are always suffixed with "Widget".
    *
    * @param string $class
    * @return string
    */
    public function prepare_classname($class)
    {
        return $class.'Widget';
    }
}
