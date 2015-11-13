<?php

namespace Charcoal\App\Action;

// Module `charcoal-core` dependencies
use \Charcoal\Core\IdentFactory as IdentFactory;

/**
* The ActionFactory creates Action objects.
*
* @see \Charcoal\Core\FactoryInterface
*/
class ActionFactory extends IdentFactory
{
    /**
    * @param array $data
    */
    public function __construct()
    {
        $this->set_base_class('\Charcoal\Action\ActionInterface');
    }

    /**
    * IdentFactory > prepare_classname()
    *
    * Actions class names are always suffixed with "Action".
    *
    * @param string $class
    * @return string
    */
    public function prepare_classname($class)
    {
        return $class.'Action';
    }
}
