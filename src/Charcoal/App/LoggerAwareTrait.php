<?php

namespace Charcoal\App;

// PSR-3 logger
use \Psr\Log\LoggerInterface;

/**
 * A simple implementation of `Charcoal\App\Log\LoggerAwareInterface`.
 */
trait LoggerAwareTrait
{
    /**
     * The PSR-3 logger instance
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Alias of {@see self::set_logger()}
     *
     * @see   LoggerAwareInterface::setLogger() Fulfills the PSR-1 / PSR-3 style
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        return $this->set_logger($logger);
    }

    /**
     * Set a logger
     *
     * @param  LoggerInterface $logger
     * @return self
     */
    public function set_logger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }
}
