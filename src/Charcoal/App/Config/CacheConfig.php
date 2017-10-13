<?php

namespace Charcoal\App\Config;

use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Cache Configuration
 */
class CacheConfig extends AbstractConfig
{
    const DEFAULT_NAMESPACE = 'charcoal';

    /**
     * Human-readable intervals in seconds.
     */
    const HOUR_IN_SECONDS = 3600;
    const DAY_IN_SECONDS  = 86400;
    const WEEK_IN_SECONDS = 604800;

    /**
     * Whether to enable or disable the cache service.
     *
     * Note:
     * - When TRUE, the {@see self::$types} are used.
     * - When FALSE, the "memory" type is used.
     *
     * @var boolean
     */
    private $active = true;

    /**
     * Cache type(s) to use.
     *
     * Represents a cache driver.
     *
     * @var array
     */
    private $types = [ 'memory' ];

    /**
     * Time-to-live in seconds.
     *
     * @var integer
     */
    private $defaultTtl = self::WEEK_IN_SECONDS;

    /**
     * Cache namespace.
     *
     * @var string
     */
    private $prefix = self::DEFAULT_NAMESPACE;

    /**
     * Retrieve the default values.
     *
     * @return array
     */
    public function defaults()
    {
        return [
            'active'      => true,
            'types'       => [ 'memory' ],
            'default_ttl' => self::WEEK_IN_SECONDS,
            'prefix'      => self::DEFAULT_NAMESPACE
        ];
    }

    /**
     * Enable / Disable the cache service.
     *
     * @param  boolean $active The active flag;
     *     TRUE to enable, FALSE to disable.
     * @return CacheConfig Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * Determine if the cache service is enabled.
     *
     * @return boolean TRUE if enabled, FALSE if disabled.
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * Set the cache type(s) to use.
     *
     * The first cache actually available on the system will be the one used for caching.
     *
     * @param  string[] $types One or more types to try as cache driver until success.
     * @return CacheConfig Chainable
     */
    public function setTypes(array $types)
    {
        $this->types = [];
        $this->addTypes($types);
        return $this;
    }

    /**
     * Retrieve the available cache types.
     *
     * @return string[]
     */
    public function validTypes()
    {
        return [
            'apc',
            'file',
            'db',
            'memcache',
            'memory',
            'noop',
            'redis'
        ];
    }

    /**
     * Add cache type(s) to use.
     *
     * @param  string[] $types One or more types to try as cache driver until success.
     * @return CacheConfig Chainable
     */
    public function addTypes(array $types)
    {
        foreach ($types as $type) {
            $this->addType($type);
        }
        return $this;
    }

    /**
     * Add a cache type to use.
     *
     * @param  string $type The cache type.
     * @throws InvalidArgumentException If the type is not a string or unsupported.
     * @return CacheConfig Chainable
     */
    public function addType($type)
    {
        if (!in_array($type, $this->validTypes())) {
            throw new InvalidArgumentException(
                sprintf('Invalid cache type: "%s"', $type)
            );
        }

        $this->types[] = $type;
        return $this;
    }

    /**
     * Retrieve the cache type(s) to use.
     *
     * @return array
     */
    public function types()
    {
        return $this->types;
    }

    /**
     * Set the default time-to-live for cached items.
     *
     * @param  integer $ttl The time-to-live, in seconds.
     * @throws InvalidArgumentException If the TTL is not numeric.
     * @return CacheConfig Chainable
     */
    public function setDefaultTtl($ttl)
    {
        if (!is_numeric($ttl)) {
            throw new InvalidArgumentException(
                'TTL must be an integer (seconds).'
            );
        }

        $this->defaultTtl = (int)$ttl;
        return $this;
    }

    /**
     * Retrieve the default time-to-live for cached items.
     *
     * @return integer
     */
    public function defaultTtl()
    {
        return $this->defaultTtl;
    }

    /**
     * Set the cache namespace.
     *
     * @param  string $prefix The cache prefix (or namespace).
     * @throws InvalidArgumentException If the prefix is not a string.
     * @return CacheConfig Chainable
     */
    public function setPrefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException(
                'Prefix must be a string.'
            );
        }

        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Retrieve the cache namespace.
     *
     * @return string
     */
    public function prefix()
    {
        return $this->prefix;
    }
}
