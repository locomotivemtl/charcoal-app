<?php

namespace Charcoal\App\Route;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
*
*/
interface RouteInterface
{
    /**
    *
    */
    public function __invoke(RequestInterface $request, ResponseInterface $response);
}
