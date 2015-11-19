<?php

namespace Charcoal\App\Template;

/**
*
*/
interface WidgetInterface
{
    /**
    * @param boolean $active
    * @return WidgetInterface Chainable
    */
    public function set_active($active);

    /**
    * @return boolean
    */
    public function active();
}
