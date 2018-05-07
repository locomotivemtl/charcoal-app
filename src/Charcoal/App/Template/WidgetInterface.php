<?php

namespace Charcoal\App\Template;

// From Pimple
use Pimple\Container;

/**
 *
 */
interface WidgetInterface
{
    /**
     * @param array $data The template data to set.
     * @return WidgetInterface Chainable
     */
    public function setData(array $data);

    /**
     * @param boolean $active The active flag.
     * @return WidgetInterface Chainable
     */
    public function setActive($active);

    /**
     * @return boolean
     */
    public function active();
}
