<?php

namespace Charcoal\App\Template;

/**
 *
 */
interface WidgetInterface
{
    /**
     * @param array|\ArrayInterface $data The template data to set.
     * @return WidgetInterface Chainable
     */
    public function setData($data);

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
