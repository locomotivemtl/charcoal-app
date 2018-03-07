<?php

namespace Charcoal\App\Http;

// From PSR-7
use Psr\Http\Message\UriInterface as PsrUriInterface;

// From Slim
use Slim\Http\Uri as SlimUri;

// From 'charcoal-app'
use Charcoal\App\Http\UriInterface;

/**
 * Value object representing a URI.
 */
class Uri extends SlimUri implements
    UriInterface
{
    /**
     * Retrieve the username and password component of the URI.
     *
     * @return array The URI user information, in `[ username, password ]` format.
     */
    public function getUserInfoAsArray()
    {
        return [ $this->user, $this->password ];
    }

    /**
     * Return an instance with the specified user information.
     *
     * @param  string|array $user     The user name to use for authority.
     *     The first argument can also be an array `[ 'user', 'password' ]`
     *     or a string using a colon (":") to separate the values.
     * @param  string|null  $password The password associated with $user.
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $user;

        if ($password === null && strpos($user, ':')) {
            $info = explode(':', $user);
        }

        if (is_array($info)) {
            list($user, $password) = array_pad($info, 2, null);
        }

        return parent::withUserInfo($user, $password);
    }

    /**
     * Determine if the given URI has a scheme.
     *
     * A relative reference that begins with a single slash character is termed an absolute-path reference.
     *
     * @link   https://tools.ietf.org/html/rfc3986#section-4
     * @return boolean
     */
    public function isAbsolute()
    {
        return $this->getScheme() !== '';
    }

    /**
     * Whether the URI is a network-path reference.
     *
     * @link   https://tools.ietf.org/html/rfc3986#section-4.2
     * @return boolean
     */
    public function isNetworkPath()
    {
        return $this->getScheme() === ''
            && $this->getAuthority() !== '';
    }

    /**
     * Determine if the given URI is a relative-path reference.
     *
     * @link   https://tools.ietf.org/html/rfc3986#section-4.2
     * @return boolean
     */
    public function isRelativePath()
    {
        return $this->getScheme() === ''
            && $this->getAuthority() === ''
            && (!isset($this->getPath()[0]) || $this->getPath()[0] !== '/');
    }

    /**
     * Determine if the given URI is a absolute-path reference.
     *
     * @link   https://tools.ietf.org/html/rfc3986#section-4.2
     * @return boolean
     */
    public function isAbsolutePath()
    {
        return $this->getScheme() === ''
            && $this->getAuthority() === ''
            && isset($this->getPath()[0])
            && $this->getPath()[0] === '/';
    }
}
