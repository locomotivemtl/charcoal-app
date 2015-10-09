<?php

namespace Charcoal\App\Route;

/**
*
*/
interface RouteInterface
{
    /**
    *
    */
    public function __invoke($request, $response);
}
