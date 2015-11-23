<?php

namespace Charcoal\App\Language;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\App\Language\LanguageInterface;
use \Charcoal\Translation\TranslationStringInterface;
use \Charcoal\Translation\TranslationString;

/**
*
*/
class Language implements LanguageInterface
{
    /**
    * The language identifier, commonly a ISO 639 code
    *
    * @var string $ident
    */
    private $ident;

    /**
    * The language name
    *
    * @var TranslationString $name;
    */
    private $name;

    /**
    * The language locale
    *
    * @var string $locale;
    */
    private $locale;

    /**
    * @param array $data
    * @return self
    */
    public function set_data(array $data)
    {
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['name']) && $data['name'] !== null) {
            $this->set_name($data['name']);
        }
        return $this;
    }

    /**
    * Set the language identifier
    *
    * A valid ISO 639 code is recommended (either 639-1, 639-2, or 639-3).
    * Within a routable application, the identifier is most often used
    * as a base URI path.
    *
    * @param  string $ident Language identifier
    * @return self
    */
    public function set_ident($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    /**
    * Get the language identifier
    *
    * @return string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * Set the name of the language and, optionally,
    * the name translated in other languages.
    *
    * @param  TranslationString|array|string $name {
    *     The language's name in one or more languages.
    *
    *     Accept 3 types of arguments:
    *     - object (TranslationStringInterface): The data will be copied from the object's.
    *     - array: All languages available in the array. The format of the array should
    *       be a hash in the `lang` => `string` format.
    *     - string: The value will be assigned to the current language.
    * }
    * @return self
    */
    public function set_name($name)
    {
        if ($name instanceof TranslationStringInterface) {
            $config = $name->available_langs();
        } elseif (is_array($name)) {
            $config = [ 'languages' => array_keys($name) ];
        } else {
            $config = null;
        }
        $this->name = new TranslationString($name, $config);
        return $this;
    }

    /**
    * Get the name of the language
    *
    * @return TranslationString
    */
    public function name()
    {
        return $this->name;
    }

    /**
    * Alias of self::name()
    *
    * @return string
    */
    public function __toString()
    {
        return (string)$this->name();
    }

    /**
    * Set the language's locale
    *
    * @link   http://www.faqs.org/rfcs/rfc4646.html Tags for Identifying Languages
    * @link   http://www.faqs.org/rfcs/rfc4647.html Matching of Language Tags
    * @param  string $ident Language identifier
    * @return self
    *
    * @todo   Implement proper ISO 639 sanitization
    */
    public function set_locale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
    * Get the language's locale
    *
    * @return string
    */
    public function locale()
    {
        return $this->locale;
    }

    /**
    * Get the language's ISO 639-1 (alpha-2) code.
    *
    * @link   http://www.iso.org/iso/home/standards/language_codes.htm for specification
    * @return string
    *
    * @todo   Implement proper ISO 639 sanitization
    * @todo   Added support for retrieving codes in 639-1, 639-2/B, 639-2/T, and 639-3
    */
    public function iso639()
    {
        return $this->ident();
    }
}
