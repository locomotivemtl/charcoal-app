<?php

namespace Charcoal\App;

interface AppInterface
{
    /**
     * @return ModuleManager
     */
    public function module_manager();

    /**
     * @return RouteManager
     */
    public function route_manager();

    /**
     * @return MiddlewareManager
     */
    public function middleware_manager();

    /**
     * @return LanguageManager
     */
    public function language_manager();

    /**
     * @param boolean $silent If true, run in silent mode (no response).
     * @return App Chainable
     */
    public function run($silent = false);
}
