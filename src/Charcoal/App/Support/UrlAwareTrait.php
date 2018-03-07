<?php

namespace Charcoal\App\Support;

use RuntimeException;

// From PSR-7
use Psr\Http\Message\UriInterface;

/**
 * Mixin for objects that depend on the application configset.
 */
trait UrlAwareTrait
{
    /**
     * The base URI.
     *
     * @var UriInterface
     */
    protected $baseUrl;

    /**
     * Set the base URI of the application.
     *
     * @see    \Charcoal\App\ServiceProvider\AppServiceProvider `$container['base-url']`
     * @param  UriInterface $uri The base URI.
     * @return self
     */
    protected function setBaseUrl(UriInterface $uri)
    {
        $this->baseUrl = $uri;
        return $this;
    }

    /**
     * Get the base URI of the application.
     *
     * @return UriInterface
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Build the URL for the given path including the base URI.
     *
     * @param  UriInterface|string $rel A relative URI.
     * @return UriInterface
     */
    protected function getUrlFor($rel)
    {
        $base = $this->getBaseUrl();
        return $this->resolveRelativeUri($base, $rel);
    }

    /**
     * Build an absolute URI from a relative URI against a base URI.
     *
     * @param  UriInterface|string $base The base URI.
     * @param  UriInterface|string $rel  The relative URI.
     * @return UriInterface|string
     */
    protected function resolveRelativeUri($base, $rel)
    {
        if (!($base instanceof UriInterface)) {
            $base = Uri::createFromString($base);
        }

        if ((string)$rel === '') {
            return $base;
        }

        if (!($rel instanceof UriInterface)) {
            $rel = Uri::createFromString($rel);
        }

        if (!$this->isRelativeUri($rel)) {
            return $rel;
        }

        $parts = parse_url($rel);
        $user  = isset($parts['user']) ? $parts['user'] : '';
        $pass  = isset($parts['pass']) ? $parts['pass'] : null;
        $port  = isset($parts['port']) ? $parts['port'] : null;
        $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
        $query = isset($parts['query']) ? $parts['query'] : '';
        $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

        $uri = $base->withPath($path)->withQuery($query)->withFragment($hash);

        $info = $rel->getUserInfo();
        if ($user || $pass) {
            $uri = $uri->withUserInfo($user, $pass);
        }

        return $uri;
    }
}
