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
     * Set dependencies from the service locator.
     *
     * @param  ContainerInterface $container A service locator.
     * @return void
     */
    public function setDependencies(Container $container);

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
