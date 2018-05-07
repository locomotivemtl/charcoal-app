<?php

namespace Charcoal\App\Template;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From Pimple
use Pimple\Container;

// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;

// From 'charcoal-app'
use Charcoal\App\Template\WidgetInterface;

/**
 *
 */
abstract class AbstractWidget extends AbstractEntity implements
    WidgetInterface,
    LoggerAwareInterface,
    ViewableInterface
{
    use LoggerAwareTrait;
    use ViewableTrait;

    /**
     * @var boolean $active
     */
    private $active = true;

    /**
     * @param array|\ArrayAccess $data Optional dependencies.
     */
    public function __construct($data = null)
    {
        $this->setLogger($data['logger']);

        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * @param boolean $active The active flag.
     * @return AbstractWidget Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * @return boolean
     */
    public function active()
    {
        return $this->active;
    }

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
    protected function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children template classes.
        $this->setView($container['view']);
    }
}
