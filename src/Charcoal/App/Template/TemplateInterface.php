<?php

namespace Charcoal\App\Template;

// slim/slim dependencies
use \Slim\App as SlimApp;

/**
 *
 */
interface TemplateInterface
{
    /**
     * @param SlimApp $app The Slim app instance.
     * @return App Chainable
     */
    public function set_app(SlimApp $app);

    /**
     * @return SlimApp
     */
    public function app();
}
