<?php

namespace Charcoal\App\Handler;

use RuntimeException;

// From 'charcoal-app'
use Charcoal\App\Handler\HandlerInterface;

/**
 * The Handler Aware Trait provides the methods necessary for an object
 * to use a "Handler" object.
 */
trait HandlerAwareTrait
{
    /**
     * The Handler object.
     *
     * @var HandlerInterface
     */
    private $appHandler;

    /**
     * Set the handler object.
     *
     * @param  HandlerInterface $handler The Handler object.
     * @return void
     */
    protected function setAppHandler(HandlerInterface $handler)
    {
        $this->appHandler = $handler;
    }

    /**
     * Retrieve the handler object.
     *
     * @throws RuntimeException If the handler is accessed before having been set.
     * @return HandlerInterface
     */
    protected function appHandler()
    {
        if ($this->appHandler === null) {
            throw new RuntimeException(
                'App handler has not been set on this (handler aware) object.'
            );
        }
        return $this->appHandler;
    }
}
