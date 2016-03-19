<?php

namespace Charcoal\App\Handler;

// Dependency from Pimple
use \Pimple\Container;

/**
 * Request Handler
 */
interface HandlerInterface
{
    /**
     * Return a new HandlerInterface object.
     *
     * @param Container $container A dependencies container instance.
     */
    public function __construct(Container $container);

    /**
     * Initialize the HandlerInterface object.
     *
     * @return HandlerInterface Chainable
     */
    public function init();
}
