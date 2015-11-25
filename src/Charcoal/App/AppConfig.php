<?php

namespace Charcoal\App;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractConfig;

// Module `charcoal-core` dependencies
use \Charcoal\Translation\TranslationConfig;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\Language\Language;

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
    * @param array $routes
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
    * @param array $routes
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
    * @param array $modules
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
    * Set the application's available languages
    *
    * @param  Language[] $lang
    * @return self
    */
    public function set_languages(array $languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
    * Add or update an available language to the application
    *
    * @param  Language $lang
    * @return self
    */
    public function add_language(Language $lang)
    {
        $this->languages[$lang->ident()] = $lang;
        return $this;
    }

    /**
    * Get the application's list of available languages
    *
    * @return Language[]
    */
    public function languages()
    {
        return $this->languages;
    }

    /**
    * Set the application's global TranslationConfig
    *
    * @param  array|TranslationConfig $translation
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
