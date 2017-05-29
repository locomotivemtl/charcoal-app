<?php

namespace Charcoal\App\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From PSR-11
use Psr\Container\ContainerInterface;

/**
 * Base Route Interface.
 *
 * Routes are simple _invokable_ objects.
 */
interface RouteInterface
{
    /**
     * @param  ContainerInterface $container A PSR-11 compatible Container instance.
     * @param  RequestInterface   $request   A PSR-7 compatible Request instance.
     * @param  ResponseInterface  $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(ContainerInterface $container, RequestInterface $request, ResponseInterface $response);
}
