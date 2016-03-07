<?php

namespace Charcoal\App\Config;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
 * Cache Configuration
 */
class CacheConfig extends AbstractConfig
{
    /**
     * @var array $types
     */
    private $types = ['memory'];

    /**
     * @var integer $defaultTtl
     */
    private $defaultTtl = 0;

    /**
     * @var string $prefix
     */
    private $prefix = 'charcoal';

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'type'        => 'noop',
            'defaultTtl' => 0,
            'prefix'      => 'charcoal-cache-'
        ];
    }

    /**
     * Set the types (drivers) of cache.
     *
     * The first cache actually available on the system will be the one used for caching.
     *
     * @param string[] $types The types of cache to try using, in order of priority.
     * @return CacheConfig Chainable
     */
    public function setTypes(array $types)
    {
        $this->types = [];
        foreach ($types as $type) {
            $this->addType($type);
        }
        return $this;
    }

    /**
     * Get the valid types (drivers).
     *
     * @return array
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
     * @param string $type The cache type.
     * @throws InvalidArgumentException If the type is not a string.
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
     * @return array
     */
    public function types()
    {
        // @todo: Sort by priority.
        return $this->types;
    }

    /**
     * @param integer $ttl The time-to-live, in seconds.
     * @throws InvalidArgumentException If the TTL argument is not numeric.
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
     * @return integer
     */
    public function defaultTtl()
    {
        return $this->defaultTtl;
    }

    /**
     * @param string $prefix The cache prefix (or namespace).
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
     * @return string
     */
    public function prefix()
    {
        return $this->prefix;
    }
}
