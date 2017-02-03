<?php

namespace Charcoal\App\Template;

// Dependencies from `Pimple`
use \Pimple\Container;

/**
 *
 */
interface WidgetInterface
{
    /**
     * Give an opportunity to children classes to inject dependencies from a Pimple Container.
     *
     * Does nothing by default, reimplement in children classes.
     *
     * The `$container` DI-container (from `Pimple`) should not be saved or passed around, only to be used to
     * inject dependencies (typically via setters).
     *
     * @param Container $container A dependencies container instance.
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
