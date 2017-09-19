<?php

namespace Charcoal\App\Middleware;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The IP middleware can restrict access to certain routes to certain IP.
 */
class IpMiddleware
{
    /**
     * @var array|null
     */
    private $blacklist;

    /**
     * @var array|null
     */
    private $whitelist;

    /**
     * @var string|null
     */
    private $blacklistedRedirect;

    /**
     * @var string|null
     */
    private $notWhitelistedRedirect;

    /**
     * @var boolean
     */
    private $failOnInvalidIp;

    /**
     * @param array $data Constructor dependencies and options.
     */
    public function __construct(array $data)
    {
        $data = array_merge($this->defaults(), $data);

        $this->blacklist = $data['blacklist'];
        $this->whitelist = $data['whitelist'];

        $this->blacklistedRedirect    = $data['blacklisted_redirect'];
        $this->notWhitelistedRedirect = $data['not_whitelisted_redirect'];

        $this->failOnInvalidIp = $data['fail_on_invalid_ip'];
    }

    /**
     * Default middleware options.
     *
     * @return array
     */
    public function defaults()
    {
        return [
            'blacklist' => null,
            'whitelist' => null,

            'blacklisted_redirect'     => null,
            'not_whitelisted_redirect' => null,

            'fail_on_invalid_ip' => false
        ];
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
        $ip = $this->getClientIp($request);
        if (!$ip) {
            if ((!empty($this->blacklist) || !empty($this->whitelist)) && $this->failOnInvalidIp === true) {
                return $response->withStatus(403);
            } else {
                return $next($request, $response);
            }
        }

        // Check blacklist.
        if ($this->isIpBlacklisted($ip) === true) {
            if ($this->blacklistedRedirect) {
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', $this->blacklistedRedirect);
            } else {
                // IP explicitely blacklisted: forbidden
                return $response->withStatus(403);
            }
        }

        // Check whitelist.
        if ($this->isIpWhitelisted($ip) === false) {
            if ($this->notWhitelistedRedirect) {
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', $this->blacklistedRedirect);
            } else {
                // IP not whistelisted: forbidden
                return $response->withStatus(403);
            }
        }

        // If here, not blacklisted or not-whitelisted; continue as normal.
        return $next($request, $response);
    }

    /**
     * Check wether a certain IP is explicitely blacklisted.
     *
     * If the blacklist is null or empty, then nothing is ever blacklisted (return false).
     *
     * Note: this method only performs an exact string match on IP address, no IP masking / range features.
     *
     * @param string $ip The IP address to check against the blacklist.
     * @return boolean
     */
    private function isIpBlacklisted($ip)
    {
        if (empty($this->blacklist)) {
            return false;
        }
        return $this->isIpInRange($ip, $this->blacklist);
    }

    /**
     * Check wether a certain IP is explicitely whitelisted.
     *
     * If the whitelist is null or empty, then all IPs are whitelisted (return true).
     *
     * Note; This method only performs an exact string match on IP address, no IP masking / range features.
     *
     * @param string $ip The IP address to check against the whitelist.
     * @return boolean
     */
    private function isIpWhitelisted($ip)
    {
        if (empty($this->whitelist)) {
            return true;
        }
        return $this->isIpInRange($ip, $this->whitelist);
    }


    /**
     * @param string   $ip    The IP to check.
     * @param string[] $cidrs The array of IPs/CIDRs to validate against. "/32" netmask is assumed.
     * @return boolean
     */
    private function isIpInRange($ip, array $cidrs)
    {
        foreach ($cidrs as $range) {
            if (strpos($range, '/') === false) {
                $range .= '/32';
            }
            // $range is in IP/CIDR format eg 127.0.0.1/24
            list($subnet, $netmask) = explode('/', $range, 2);
            $netmask = ~(pow(2, (32 - $netmask)) - 1);
            if ((ip2long($ip) & $netmask) == (ip2long($subnet) & $netmask)) {
                return true;
            }
        }
        // Nothing matched
        return false;
    }

    /**
     * @param RequestInterface $request The PSR-7 HTTP request.
     * @return string
     */
    private function getClientIp(RequestInterface $request)
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        }
        return '';
    }
}
