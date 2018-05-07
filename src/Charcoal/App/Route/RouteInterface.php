<?php

namespace Charcoal\App\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

/**
 * Base Route Interface.
 *
 * Routes are simple _invokable_ objects.
 */
interface RouteInterface
{
    /**
     * @param Container         $container A DI container (pimple) instance.
     * @param RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(Container $container, RequestInterface $request, ResponseInterface $response);
}
