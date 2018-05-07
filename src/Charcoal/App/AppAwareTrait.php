<?php

namespace Charcoal\App;

// From 'charcoal-app'
use Charcoal\App\App;

/**
* Implementation, as trait, of the `AppAwareInterface`.
*/
trait AppAwareTrait
{
    /**
     * @var App $app
     */
    private $app;

    /**
     * @param App $app The app instance this object depends on.
     * @return void
     */
    protected function setApp(App $app)
    {
        $this->app = $app;
    }

    /**
     * @return App
     */
    protected function app()
    {
        return $this->app;
    }
}
