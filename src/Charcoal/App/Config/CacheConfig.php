<?php

namespace Charcoal\App\Config;

// Dependencies from `PHP`
use InvalidArgumentException;

// Module `charcoal-config` dependencies
use Charcoal\Config\AbstractConfig;

/**
 * Cache Configuration
 */
class CacheConfig extends AbstractConfig
{
    /**
     * @var array
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
     * @var array
     */
    public $middleware;

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'types'        => ['memory'],
            'default_ttl' => 0,
            'prefix'      => 'charcoal',
            'middleware'    => $this->middlewareDefaults()
        ];
    }

    /**
     * @return array
     */
    private function middlewareDefaults()
    {
        return [
            'included_path'  => '',
            'excluded_path'  => '*',
            'methods'        => [
                'GET'
            ],
            'status_codes'   => [
                200
            ],
            'ttl'            => 0,
            'included_query' => null,
            'excluded_query' => '*',
            'ignored_query'  => null
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

    /**
     * @param array $middleware The cache middleware configuration.
     * @return CacheConfig Chainable
     */
    public function setMiddleware(array $middleware)
    {
        $middleware = array_merge($this->middlewareDefaults(), $middleware);
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * @return array
     */
    public function middleware()
    {
        return $this->middleware;
    }
}
