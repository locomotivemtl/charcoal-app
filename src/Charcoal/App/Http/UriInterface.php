<?php

namespace Charcoal\App\Http;

// From PSR-7
use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * Value object representing a URI.
 *
 * Note: These methods are not part of the PSR-7 standard.
 *
 * @link http://tools.ietf.org/html/rfc3986 (the URI specification)
 */
interface UriInterface extends PsrUriInterface
{
    /**
     * Retrieve the base path segment of the URI.
     *
     * This method MUST return a string; if no path is present it MUST return
     * an empty string.
     *
     * @return string The base path segment of the URI.
     */
    public function getBasePath();

    /**
     * Set the base path.
     *
     * @param  string $basePath The base path to use with the new instance.
     * @return static
     */
    public function withBasePath($basePath);
}
