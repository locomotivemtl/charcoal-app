<?php

namespace Charcoal\App\Language;

interface LanguageInterface
{
    /**
    * @param array $data
    * @return LanguageInterface Chainable
    */
    public function set_data(array $data);

    /**
    * Set the language identifier
    *
    * @param  string $ident
    * @return LanguageInterface Chainable
    */
    public function set_ident($ident);

    /**
    * Get the language identifier
    *
    * @return string
    */
    public function ident();

    /**
    * Set the name of the language and, optionally,
    * the name translated in other languages.
    *
    * @param  TranslationString|array|string $name
    * @return LanguageInterface Chainable
    */
    public function set_name($name);

    /**
    * Get the name of the language
    *
    * @return string
    */
    public function name();

    /**
    * Set the language's locale
    *
    * @param  string $ident
    * @return LanguageInterface Chainable
    */
    public function set_locale($locale);

    /**
    * Get the language's locale
    *
    * @return string
    */
    public function locale();

    /**
    * Get the language's ISO 639 code.
    *
    * @return string
    */
    public function iso639();
}
