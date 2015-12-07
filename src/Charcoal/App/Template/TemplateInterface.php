<?php

namespace Charcoal\App\Template;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppInterface;

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
     * @param AppInterface $app The Charcoal Application instance.
     * @return TemplateInterface Chainable
     */
    public function set_app(AppInterface $app);

    /**
     * @return AppInterface
     */
    public function app();
}
