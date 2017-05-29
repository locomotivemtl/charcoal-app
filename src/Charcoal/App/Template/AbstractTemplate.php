<?php

namespace Charcoal\App\Template;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From PSR-11
use Psr\Container\ContainerInterface;

// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;

// From 'charcoal-app'
use Charcoal\App\Template\TemplateInterface;

/**
 * Template (View Controller) base class
 */
abstract class AbstractTemplate extends AbstractEntity implements
    LoggerAwareInterface,
    TemplateInterface
{
    use LoggerAwareTrait;

    /**
     * @param array|\ArrayAccess $data The dependencies (app and logger).
     */
    public function __construct($data = null)
    {
        $this->setLogger($data['logger']);

        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * Initialize the template with a request.
     *
     * @param RequestInterface $request The request to intialize.
     * @return boolean Success / Failure.
     */
    public function init(RequestInterface $request)
    {
        // This method is a stub. Reimplement in children methods to ensure template initialization.
        return true;
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
}
