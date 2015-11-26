<?php

namespace Charcoal\App\Language;

interface LanguageInterface
{
    /**
     * @param array $data The data to set.
     * @return LanguageInterface Chainable
     */
    public function set_data(array $data);

    /**
     * Set the language identifier
     *
     * @param  string $ident The language identifier.
     * @return LanguageInterface Chainable
     */
    public function set_ident($ident);

    /**
     * Get the language identifier
     *
     * @return string Language identifier.
     */
    public function ident();

    /**
     * Set the name of the language and, optionally,
     * the name translated in other languages.
     *
     * @param  TranslationString|array|string $name The language's name in one or more languages.
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
     * @param  string $locale The language's locale code.
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
