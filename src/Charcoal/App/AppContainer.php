<?php

namespace Charcoal\App;

// Slim Dependencies
use \Slim\Container;

// Intra-Module `charcoal-app` dependencies
use \Charcoal\App\Provider\AppServiceProvider;
use \Charcoal\App\Provider\CacheServiceProvider;
use \Charcoal\App\Provider\DatabaseServiceProvider;
use \Charcoal\App\Provider\LoggerServiceProvider;
use \Charcoal\App\Provider\TranslatorServiceProvider;
use \Charcoal\App\Provider\ViewServiceProvider;

/**
 * Charcoal App Container
 */
class AppContainer extends Container
{
    /**
     * Create new container
     *
     * @param array $values The parameters or objects.
     */
    public function __construct(array $values = [])
    {
        $config = isset($values['config']) ? $values['config'] : [];

        $this['charcoal/app/config'] = $config;
        $this['config'] = $this['charcoal/app/config'];

        // Default Services
        $this->register(new AppServiceProvider());
        $this->register(new CacheServiceProvider());
        $this->register(new DatabaseServiceProvider());
        $this->register(new LoggerServiceProvider());
        $this->register(new TranslatorServiceProvider());
        $this->register(new ViewServiceProvider());

        // Slim container
        parent::__construct($values);
    }
}
