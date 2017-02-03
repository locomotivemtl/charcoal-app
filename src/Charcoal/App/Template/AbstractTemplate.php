<?php

namespace Charcoal\App\Template;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// PSR-7 (HTTP Messaging) dependencies
use \Psr\Http\Message\RequestInterface;

// Dependencies from `Pimple`
use \Pimple\Container;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractEntity;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\Template\TemplateInterface;

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
    public function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children template classes.
    }
}
