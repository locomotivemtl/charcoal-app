<?php

namespace Charcoal\App;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface as PsrLoggerAwareInterface;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface extends PsrLoggerAwareInterface
{
    /**
     * Set a logger
     *
     * @param  LoggerInterface $logger
     * @return self
     */
    public function set_logger(LoggerInterface $logger = null);

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger();
}
