<?php

namespace Charcoal\App\Handler;

// From PSR-3
use Psr\Log\LoggerAwareInterface;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;

/**
 * Request Handler
 */
interface HandlerInterface extends
    ConfigurableInterface,
    LoggerAwareInterface,
    ViewableInterface
{
}
