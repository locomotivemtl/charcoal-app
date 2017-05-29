<?php

namespace Charcoal\App\Template;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From PSR-11
use Psr\Container\ContainerInterface;

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
     * Set dependencies from the service locator.
     *
     * Serves as the entry point for subclasses to retrieve their dependencies from the container.
     *
     * By default, this method does nothing and should be reimplemented in subclasses.
     *
     * The container should not be saved or passed around, only to be used to
     * inject dependencies (typically via setters).
     *
     * @param  ContainerInterface $container A service locator.
     * @return void
     */
    public function setDependencies(ContainerInterface $container)
    {
        // This method is a stub. Reimplement in children template classes.
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
}
