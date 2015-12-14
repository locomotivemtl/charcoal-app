<?php

namespace Charcoal\App;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractConfig;

// Module `charcoal-core` dependencies
use \Charcoal\Translation\TranslationConfig;

/**
 * Charcoal App configuration
 */
class AppConfig extends AbstractConfig
{
    /**
     * @var array $routes
     */
    private $routes = [];

    /**
     * @var array $routables
     */
    private $routables = [];

    /**
     * @var array $modules
     */
    private $modules = [];

    /**
     * Set the application's absolute root path.
     *
     * @param string $path The absolute path to the application's root directory.
     * @return AppConfig Chainable
     */
    public function set_ROOT($path)
    {
        $this->ROOT = rtrim(realpath($path), '/').'/';
        return $this;
    }

    /**
     * Retrieve the application's absolute root path.
     *
     * @return string The absolute path to the application's root directory.
     */
    public function ROOT()
    {
        return $this->ROOT;
    }

    /**
     * Retrieve the application's default timezone.
     *
     * Will be used by the PHP date and date-time functions.
     *
     * @return string
     */
    public function timezone()
    {
        if (isset($this->timezone)) {
            return $this->timezone;
        } else {
            return 'UTC';
        }
    }

    /**
     * @param array $routes The route configuration structure to set.
     * @return AppConfig Chainable
     */
    public function set_routes(array $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return array
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * @param array $routables The routable configuration structure to set.
     * @return AppConfig Chainable
     */
    public function set_routables(array $routables)
    {
        $this->routables = $routables;
        return $this;
    }

    /**
     * @return array
     */
    public function routables()
    {
        return $this->routables;
    }

    /**
     * @param array $modules The module configuration structure to set.
     * @return AppConfig Chainable
     */
    public function set_modules(array $modules)
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * @return array
     */
    public function modules()
    {
        return $this->modules;
    }

    /**
     * Set the application's global TranslationConfig
     *
     * @param  array|TranslationConfig $translation The Translation Configuration.
     * @return self
     */
    public function set_translation($translation)
    {
        if ($translation instanceof TranslationConfig) {
            $this->translation_config = $translation;
        } elseif (is_array($translation)) {
            $this->translation_config = new TranslationConfig($translation);
        }
        return $this;
    }

    /**
     * Get the application's global TranslationConfig
     *
     * @return TranslationConfig
     */
    public function translation()
    {
        return $this->translation_config;
    }
}
