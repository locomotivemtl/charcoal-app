<?php

namespace Charcoal\App\Language;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;
use \Charcoal\App\Language\Language;
use \Charcoal\App\Language\LanguageInterface;
use \Charcoal\Config\GenericConfig;
use \Charcoal\Translation\TranslationString;
use \Charcoal\Translation\TranslationConfig;

/**
*
*/
class LanguageManager extends AbstractManager
{
    /**
    * @var LanguageInterface[]
    */
    private $available_langs;

    /**
    * @var TranslationConfig
    */
    private $translation_config;

    /**
    * @var GenericConfig
    */
    private static $language_index;

    /**
    * Set up the available languages, defaults, and active
    *
    * @return void
    */
    public function setup()
    {
        $this->setup_translations();
        $this->setup_languages();
    }

    /**
    * Set up the available languages, defaults, and active
    *
    * Settings:
    * - languages
    * - default_lang
    *
    * @return void
    */
    public function setup_translations()
    {
        $config = $this->config();

        $translation_config = [];

        if (isset($config['default_language'])) {
            $translation_config['default_language'] = $config['default_language'];
        }

        if (isset($config['languages'])) {
            $available_langs = array_filter($config['languages'], function ($config) {
                return (!isset($config['active']) || $config['active']);
            });

            if (count($available_langs)) {
                $translation_config['languages'] = array_keys($available_langs);
            }
        }

        $this->set_translation($translation_config);
    }

    /**
    * Set up the available languages, defaults, and active
    *
    * @return void
    */
    public function setup_languages()
    {
        $config = $this->config();
        $index  = self::language_index();

        if (isset($config['languages'])) {
            $available_langs = $config['languages'];
        } else {
            $available_langs = $this->translation()->available_langs();
        }

        foreach ($available_langs as $a => $b) {
            $ident = null;
            $data  = [];
            $lang  = new Language();

            if (is_string($b)) {
                $ident = $b;
            } elseif (is_string($a)) {
                $ident = $a;
            }

            if (is_array($b)) {
                $data = $b;
            }

            if (isset($index[$ident])) {
                $data = array_merge($index[$ident], $data);
            }

            if ($ident && !isset($data['ident'])) {
                $lang->set_ident($ident);
            }

            $lang->set_data($data);

            if ($lang->ident() === null) {
                $this->resolve_ident($lang, $ident);
            }

            $this->add_language($lang);
        }
    }

    /**
     * Get a list of all existing languages, their names and translations,
     * codes in various standards, and directionality.
     *
     * @return GenericConfig
     */
    public static function language_index()
    {
        if (!isset(self::$language_index)) {
            self::$language_index = new GenericConfig(__DIR__ . '/../../../../config/languages.json');
        }

        return self::$language_index;
    }

    /**
    * Resolve a string identifier for a given Language.
    *
    * @param  LanguageInterface &$lang  The instance that needs resolution
    * @param  mixed             $ident  Optional key/index of $config in previous method
    * @return void
    * @throws InvalidArgumentException
    */
    public function resolve_ident(LanguageInterface &$lang, $ident = null)
    {
        if (is_string($ident)) {
            $lang->set_ident($ident);
        } else {
            if (count($codes)) {
                $lang->set_ident($lang->code());
            }
        }

        if ($lang->ident() === null) {
            $name = (string)$lang;
            if (!$name) {
                $name = 'Language';
            }
            throw new InvalidArgumentException(
                sprintf(
                    '%s requires a string identifier (one of "ident", "%s").',
                    $name,
                    implode('", "', $tries)
                )
            );
        }
    }

    /**
    * Set the manager's available languages
    *
    * @param  LanguageInterface[] $lang
    * @return self
    * @throws InvalidArgumentException if array has a member that isn't an instance of Language
    */
    public function set_languages($languages)
    {
        $this->languages = [];

        foreach ($languages as $lang) {
            if ($lang instanceof LanguageInterface) {
                $this->add_language($lang);
            } else {
                throw new InvalidArgumentException('Must be an instance of Language.');
            }
        }
        return $this;
    }

    /**
    * Add or update an available language to the manager
    *
    * @param  LanguageInterface $lang
    * @return self
    */
    public function add_language(LanguageInterface $lang)
    {
        $this->languages[$lang->ident()] = $lang;
        return $this;
    }

    /**
    * Get the manager's list of available languages
    *
    * @return LanguageInterface[]
    */
    public function languages()
    {
        return $this->languages;
    }

    /**
    * Set the manager's translations for TranslationConfig
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
    * Get the manager's translations
    *
    * @return TranslationConfig
    */
    public function translation()
    {
        return $this->translation_config;
    }
}
