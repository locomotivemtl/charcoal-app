<?php

namespace Charcoal\App;

// From 'charcoal-app'
use Charcoal\App\App;

/**
 * Interface for objects that depend on an app.
 *
 * Mostly exists to avoid boilerplate code duplication.
 */
interface AppAwareInterface
{
    /**
     * @param App $app The app instance this object depends on.
     * @return AppAwareInterface Chainable
     */
    public function setApp(App $app);
}
