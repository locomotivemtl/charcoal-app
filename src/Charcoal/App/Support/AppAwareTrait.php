<?php

namespace Charcoal\App\Support;

use Charcoal\App\App;

/**
 * Mixin for objects that depend on the application.
 */
trait AppAwareTrait
{
    /**
     * The Charcoal Application.
     *
     * @var App
     */
    private $app;

    /**
     * Set the Charcoal application.
     *
     * @param  App $app The Charcoal application instance.
     * @return void
     */
    protected function setApp(App $app)
    {
        $this->app = $app;
    }

    /**
     * Get the Charcoal application.
     *
     * @return App|null
     */
    protected function app()
    {
        return $this->app;
    }
}
