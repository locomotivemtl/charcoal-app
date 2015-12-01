<?php

namespace Charcoal\App\Template;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\App;

/**
 *
 */
interface TemplateInterface
{

    /**
     * @param array $data The template data to set.
     * @return TemplateInterface Chainable
     */
    public function set_data(array $data);

    /**
     * @param SlimApp $app The Slim app instance.
     * @return App Chainable
     */
    public function set_app(App $app);

    /**
     * @return SlimApp
     */
    public function app();
}
