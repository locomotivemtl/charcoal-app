<?php

namespace Charcoal\App\Middleware;

// Dependencies from 'PSR-6' (Caching)
use Psr\Cache\CacheItemPoolInterface;

// Dependencies from 'PSR-7' (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * The cache loader middleware attempts to load cache from the request's path (route).
 *
 * It should be run as the first middleware of the stack, in most cases.
 * (With Slim, this means adding it last)
 *
 * There is absolutely no extra configuration or dependencies to this middleware.
 * If the cache key exists, then it will be injected in the response body and returned.
 *
 * Note that if the cache is hit, the response is directly returned; meaning the rest
 * of the middilewares in the stack will be ignored
 *
 * It is up to other means, such as the provided `CacheGeneratorMiddleware`, to set this cache entry.
 */
class CacheMiddleware
{
    /**
     * PSR-6 cache item pool.
     * @var CacheItemPool
     */
    private $cachePool;

    /**
     * @var integer
     */
    private $cacheTtl;

    /**
     * If set, only queries matching this path
     * @var null|string|array
     */
    private $includedPath;

    /**
     * Queries matching
     * @var null|string|array
     */
    private $excludedPath;

    /**
     * Only cache the request if it matches one of those methods.
     * @var string[]
     */
    private $methods;

    /**
     * Only cache the request if it matches one of those status codes.
     * @var int[]
     */
    private $statusCode;

    /**
     * @var array|string|null
     */
    private $includedQuery;

    /**
     * @var array|string|null
     */
    private $excludedQuery;

    /**
     * @var array|string|null
     */
    private $ignoredQuery;

    /**
     * @var boolean
     */
    private $headers;

    /**
     * @param array $data Constructor dependencies and options.
     */
    public function __construct(array $data)
    {
        $defaults = [
            'included_path'  => '*',
            'excluded_path'  => [
                '~^/admin\b~'
            ],
            'methods'        => [
                'GET'
            ],
            'status_codes'   => [
                200
            ],
            'ttl'            => 864000,
// 24 hours
            'included_query' => null,
            'excluded_query' => null,
            'ignored_query'  => null,
            'headers'        => true
        ];
        $data = array_replace($defaults, $data);

        $this->cachePool = $data['cache'];
        $this->cacheTtl = $data['ttl'];

        $this->includedPath = $data['included_path'];
        $this->excludedPath = $data['excluded_path'];

        $this->methods = $data['methods'];
        $this->statusCodes = $data['status_codes'];

        $this->includedQuery = $data['included_query'];
        $this->excludedQuery = $data['excluded_query'];
        $this->ignoredQuery = $data['ignored_query'];

        $this->headers = $data['headers'];
    }

    /**
     * Load a route content from path's cache.
     *
     * This method is as dumb / simple as possible.
     * It does not rely on any sort of settings / configuration.
     * Simply: if the cache for the route exists, it will be used to display the page.
     * The `$next` callback will not be called, therefore stopping the middleware stack.
     *
     * To generate the cache used in this middleware, @see \Charcoal\App\Middleware\CacheGeneratorMiddleware.
     *
     * @param RequestInterface  $request  The PSR-7 HTTP request.
     * @param ResponseInterface $response The PSR-7 HTTP response.
     * @param callable          $next     The next middleware callable in the stack.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $path = $request->getUri()->getPath();
        $queryParams = $request->getQueryParams();
        $cacheKey  = $this->cacheKey($path, $queryParams);

        if ($this->cachePool->hasItem($cacheKey)) {
            $cacheItem = $this->cachePool->getItem($cacheKey);
            $cached = $cacheItem->get();
            $response->getBody()->write($cached['body']);
            foreach ($cached['headers'] as $name => $header) {
                $response = $response->withHeader($name, $header);
            }
            return $response;
        } else {
            $response = $next($request, $response);

            if ($this->isActive($request, $response) === false) {
                return $response;
            }
            if ($this->isPathIncluded($path) === false) {
                return $response;
            }
            if ($this->isPathExcluded($path) === true) {
                return $response;
            }

            if ($this->isQueryIncluded($queryParams) === false) {
                $keyParams = $this->parseIgnoredParams($queryParams);
                if (!empty($keyParams)) {
                    return $response;
                }
            }

            if ($this->isQueryExcluded($queryParams) === true) {
                return $response;
            }


            // Nothing has excluded the cache so far: add it to the pool.
            $cacheItem = $this->cachePool->getItem($cacheKey);
            $cacheItem->expiresAfter($this->cacheTtl);
            $cacheItem = $cacheItem->set([
                'body'      => (string)$response->getBody(),
                'headers'   => (array)$response->getHeaders()
            ]);
            $this->cachePool->save($cacheItem);

            return $response;
        }
    }

    /**
     * @param string $path        The query path (route).
     * @param array  $queryParams The query parameters.
     * @return string
     */
    private function cacheKey($path, array $queryParams)
    {
        $cacheKey  = 'request/'.str_replace('/', '.', $path);
        if (!empty($queryParams)) {
            $keyParams = $this->parseIgnoredParams($queryParams);
            $cacheKey .= '.'.md5(json_encode($keyParams));
        }
        return $cacheKey;
    }


    /**
     * @param RequestInterface  $request  The PSR-7 HTTP request.
     * @param ResponseInterface $response The PSR-7 HTTP response.
     * @return boolean
     */
    private function isActive(RequestInterface $request, ResponseInterface $response)
    {
        if (!in_array($response->getStatusCode(), $this->statusCodes)) {
            return false;
        }
        if (!in_array($request->getMethod(), $this->methods)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $path The request path (route) to verify.
     * @return boolean
     */
    private function isPathIncluded($path)
    {
        if ($this->includedPath == '*') {
            return true;
        }
        if (!$this->includedPath || empty($this->includedPath)) {
            return false;
        }
        if (is_string($this->includedPath)) {
            return !!(preg_match('@'.$this->includedPath.'@', $path));
        }
        if (is_array($this->includedPath)) {
            foreach ($this->includedPath as $included) {
                if (preg_match('@'.$included.'@', $path)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * @param string $path The request path (route) to verify.
     * @return boolean
     */
    private function isPathExcluded($path)
    {
        if ($this->excludedPath == '*') {
            return true;
        }
        if (!$this->excludedPath || empty($this->excludedPath)) {
            return false;
        }
        if (is_string($this->excludedPath)) {
            return !!(preg_match('@'.$this->excludedPath.'@', $path));
        }
        if (is_array($this->excludedPath)) {
            foreach ($this->excludedPath as $excluded) {
                if (preg_match('@'.$excluded.'@', $path)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * @param array $queryParams The query parameters.
     * @return boolean
     */
    private function isQueryIncluded(array $queryParams)
    {
        if (empty($queryParams)) {
            return true;
        }
        if ($this->includedQuery == '*') {
            return true;
        }
        if (!is_array($this->includedQuery) || empty($this->includedQuery)) {
            return false;
        }
        return (count(array_intersect_key($queryParams, $this->includedQuery)) > 0);
    }

    /**
     * @param array $queryParams The query parameters.
     * @return boolean
     */
    private function isQueryExcluded(array $queryParams)
    {
        if ($this->excludedQuery == '*') {
            return true;
        }
        if (!is_array($this->excludedQuery) || empty($this->excludedQuery)) {
            return false;
        }
        if (count(array_intersect_key($queryParams, array_flip($this->excludedQuery))) > 0) {
            return true;
        }
    }

    /**
     * @param array $queryParams The query parameters.
     * @return array
     */
    private function parseIgnoredParams(array $queryParams)
    {
        if ($this->ignoredQuery == '*') {
            $ret = [];
            if (is_array($this->includedQuery)) {
                foreach ($queryParams as $k => $v) {
                    if (in_array($k, $this->includedQuery)) {
                        $ret[$k] = $v;
                    }
                }
            }
            return $ret;
        }
        if (!is_array($this->ignoredQuery) || empty($this->ignoredQuery)) {
            return $queryParams;
        }
        $ret = [];
        foreach ($queryParams as $k => $v) {
            if (!in_array($k, $this->ignoredQuery)) {
                $ret[$k] = $v;
            }
        }
        return $queryParams;
    }
}
