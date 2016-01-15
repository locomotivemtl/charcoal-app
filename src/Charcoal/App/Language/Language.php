<?php

namespace Charcoal\App\Language;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\App\Language\LanguageInterface;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Translation\TranslationStringInterface;
use \Charcoal\Translation\TranslationString;

/**
 * Language Information Object
 */
class Language implements LanguageInterface
{
    /**
     * The ID, commonly a language code
     *
     * @var string
     */
    private $ident = self::LANGUAGE_NOT_SPECIFIED;

    /**
     * The language's code(s)
     *
     * @var string[]
     */
    private $codes = [];

    /**
     * The human-readable language name(s)
     *
     * @var TranslationString
     */
    private $name;

    /**
     * The text direction of the language
     *
     * Defined using constants {@see self::DIRECTION_LTR} or {@see self::DIRECTION_RTL}.
     *
     * @var string
     */
    private $direction = '';

    /**
     * The instance's locale
     *
     * @var string
     */
    private $locale = '';

    /**
     * @param array $data The data to set.
     * @return self
     */
    public function setData(array $data)
    {
        if (isset($data['ident'])) {
            $this->setIdent($data['ident']);
        }

        if (isset($data['codes'])) {
            $this->setCodes($data['codes']);
        }

        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['direction'])) {
            $this->setDirection($data['direction']);
        }

        if (isset($data['locale'])) {
            $this->setLocale($data['locale']);
        }

        return $this;
    }

    /**
     * Set the language identifier
     *
     * A valid ISO 639 code is recommended (either 639-1, 639-2, or 639-3).
     * Within a routable application, the identifier will be
     * used as a base URI path.
     *
     * @param  string $ident Language identifier.
     * @return self
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Get the language identifier (language code)
     *
     * @return string Language identifier.
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set the name of the language
     *
     * Optionally, set the name in other languages.
     *
     * The $name parameter accepts 3 types of arguments:
     * - object (TranslationStringInterface): The data will be copied from the object's.
     * - array: All languages available in the array. The format of the array should
     *   be a hash in the `lang` => `string` format.
     * - string: The value will be assigned to the current language.
     *
     * @param TranslationString|array|string $name Language's name in one or more languages.
     * @return self
     */
    public function setName($name)
    {
        if ($name instanceof TranslationStringInterface) {
            $config = $name->languages();
        } elseif (is_array($name)) {
            $config = [
                'languages'        => array_keys($name),
                'default_language' => $this->ident()
            ];
        } else {
            $config = null;
        }

        $this->name = new TranslationString($name, $config);

        return $this;
    }

    /**
     * Get the name of the language, as an instance of TranslationString
     *
     * @return TranslationString Language's name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Alias of {@see self::name()}
     *
     * Returns the human-readable name in the current language.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name();
    }

    /**
     * Set the text direction (left-to-right or right-to-left)
     *
     * Either {@see self::DIRECTION_LTR} or {@see self::DIRECTION_RTL}.
     *
     * @param  string $dir Language's directionality.
     * @return self
     */
    public function setDirection($dir)
    {
        $this->direction = $dir;

        return $this;
    }

    /**
     * Get the text direction
     *
     * @return string Language's directionality.
     */
    public function direction()
    {
        return $this->direction;
    }

    /**
     * Set the language's locale
     *
     * @link   http://www.faqs.org/rfcs/rfc4646.html Tags for Identifying Languages
     * @link   http://www.faqs.org/rfcs/rfc4647.html Matching of Language Tags
     *
     * @param  LocaleInterface|string $locale Regional identifier.
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the language's locale
     *
     * @return string Regional identifier
     */
    public function locale()
    {
        return $this->locale;
    }

    /**
     * Set the language's code(s)
     *
     * @link   http://www.faqs.org/rfcs/rfc4646.html Tags for Identifying Languages
     * @link   http://www.faqs.org/rfcs/rfc4647.html Matching of Language Tags
     *
     * @param  string[] $codes An associative array of standards and codes.
     * @return self
     */
    public function setCodes(array $codes)
    {
        $this->codes = $codes;

        return $this;
    }

    /**
     * Get an array of the language's codes
     *
     * @return string[] An associative array of standards and codes.
     */
    public function codes()
    {
        return $this->codes;
    }

    /**
     * Add or update a language code
     *
     * @param  string $standard The registry's identifier.
     * @param  string $code     The language code.
     * @return self
     * @throws InvalidArgumentException If the $standard or $code are invalid.
     */
    public function addCode($standard, $code)
    {
        if (!is_string($standard)) {
            throw new InvalidArgumentException('Language registry ID must be a string.');
        }

        if (!is_string($code)) {
            throw new InvalidArgumentException('Language code must be a string.');
        }

        $this->codes[$standard] = $code;

        return $this;
    }

    /**
     * Get a language code
     *
     * Defaults to retrieving a language code in ISO 639-1 (alpha-2).
     *
     * Definitions:
     * • ISO 639-1 — two-letter codes for languages.
     * • ISO 639-2 — three-letter codes for languages. In general, T codes are favored; ISO 639-3 uses ISO 639-2/T.
     * • ISO 639-2/B — a "bibliographic" code, derived from the English name for the language.
     * • ISO 639-2/B — a "terminological" code, derived from the native name for the language.
     * • ISO 639-3 — three-letter codes for all known natural languages; uses the ISO 639-2 T-codes if available.
     *
     * @param  string|null $standard Optional registry to retrieve a language code in.
     * @return string
     * @throws InvalidArgumentException If the $standard is invalid.
     */
    public function code($standard = null)
    {
        if (isset($standard) && !is_string($standard)) {
            throw new InvalidArgumentException('Language registry ID must be a string or NULL.');
        }

        $this->resolveCode($standard, '639-1');

        if ($standard && isset($this->codes[$standard])) {
            return $this->codes[$standard];
        }

        return null;
    }

    /**
     * Resolve a string identifier for a given Language.
     *
     * Notes:
     * [^1]: Fix possible typos.
     * [^2]: Resolve aliases or ambiguous codes.
     *
     * @param  string $standard The language standard identifier that needs resolution.
     * @param  string $fallback Optional fallback to use if none can be found.
     * @return void
     */
    protected function resolveCode(&$standard, $fallback = null)
    {
        if (isset($standard)) {
            if (!array_key_exists($standard, $this->codes)) {
                switch (strtoupper($standard)) {
                    /** [^1] */
                    case '639-2/T':
                        $standard = '639-2T';
                        break;

                    /** [^1] */
                    case '639-2/B':
                        $standard = '639-2B';
                        break;

                    /** [^2] */
                    case '639-2':
                        if (isset($this->codes['639-2T'])) {
                            $standard = '639-2T';
                        } elseif (isset($this->codes['639-2B'])) {
                            $standard = '639-2B';
                        } elseif (isset($this->codes['639-2'])) {
                            $standard = '639-2';
                        }
                        break;

                    /** [^2] */
                    case 'ALPHA4':
                        $standard = '639-6';
                        break;

                    /** [^2] */
                    case 'ALPHA3':
                        if (isset($this->codes['639-3'])) {
                            $standard = '639-3';
                        } elseif (isset($this->codes['639-2T'])) {
                            $standard = '639-2T';
                        } elseif (isset($this->codes['639-2B'])) {
                            $standard = '639-2B';
                        } elseif (isset($this->codes['639-2'])) {
                            $standard = '639-2';
                        }
                        break;

                    /** [^2] */
                    case '639':
                    case 'ALPHA2':
                        $standard = '639-1';
                        break;
                }
            }
        } else {
            $standard = $fallback;
        }
    }
}
