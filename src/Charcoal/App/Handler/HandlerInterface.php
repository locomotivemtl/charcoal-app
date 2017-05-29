<?php

namespace Charcoal\App\Handler;

// From PSR-11
use Psr\Container\ContainerInterface;

/**
 * Request Handler
 */
interface HandlerInterface
{
    /**
     * Return a new HandlerInterface object.
     *
     * @param ContainerInterface $container A container instance.
     */
    public function __construct(ContainerInterface $container);

    /**
     * Initialize the HandlerInterface object.
     *
     * @return HandlerInterface Chainable
     */
    public function init();
}
