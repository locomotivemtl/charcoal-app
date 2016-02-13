<?php

namespace Charcoal\App;

/**
 *
 */
interface AppInterface
{
    /**
     * Retrieve the application's module manager.
     *
     * @return \Charcoal\App\Module\ModuleManager
     */
    public function moduleManager();

    /**
     * Retrieve the application's route manager.
     *
     * @return \Charcoal\App\Route\RouteManager
     */
    public function routeManager();

    /**
     * Retrieve the application's middleware manager.
     *
     * @return \Charcoal\App\Middleware\MiddlewareManager
     */
    public function middlewareManager();

    /**
     * Run application
     *
     * @param  boolean $silent If TRUE, will run in silent mode (no response).
     * @return \Psr\Http\Message\ResponseInterface The response object.
     */
    public function run($silent = false);
}
