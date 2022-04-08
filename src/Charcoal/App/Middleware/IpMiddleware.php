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
     * @var ?string[]
     */
    private $disallowed;

    /**
     * @var ?string[]
     */
    private $allowed ;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var string
     */
    private $disallowedRedirect;

    /**
     * @var string
     */
    private $notAllowedRedirect;

    /**
     * @var boolean
     */
    private $failOnInvalidIp ;

    /**
     * @param array $data Constructor dependencies and options.
     */
    public function __construct(array $data)
    {
        $data = array_merge($this->defaults(), $data);

        $this->disallowed = $data['disallowed'];
        $this->allowed = $data['allowed'];

        $this->errorMessage = $data['error_message'];

        $this->disallowedRedirect    = $data['disallowed_redirect'];
        $this->notAllowedRedirect = $data['not_allowed_redirect'];

        $this->failOnInvalidIp = $data['fail_on_invalid_ip'];
    }

    /**
     * Default middleware options.
     *
     * @return array
     */
    public function defaults() : array
    {
        return [
            'disallowed' => [],
            'allowed' => [],

            'error_message' => '',

            'disallowed_redirect'     => '',
            'not_allowed_redirect' => '',

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
        $ip = $this->getClientIp();
        if (!$ip) {
            if ((!empty($this->disallowed) || !empty($this->allowed)) && $this->failOnInvalidIp === true) {
                if ($this->errorMessage) {
                    $response->getBody()->write($this->errorMessage);
                }
                return $response->withStatus(403);
            } else {
                return $next($request, $response);
            }
        }

        // Check disallowed.
        if ($this->isIpDisallowed($ip) === true) {
            if ($this->disallowedRedirect !== '') {
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', $this->disallowedRedirect);
            } else {
                // IP explicitely disallowed: forbidden
                if ($this->errorMessage) {
                    $response->getBody()->write($this->errorMessage);
                }
                return $response->withStatus(403);
            }
        }

        // Check allowed.
        if ($this->isIpAllowed($ip) === false) {
            if ($this->notAllowedRedirect !== '') {
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', $this->notAllowedRedirect);
            } else {
                // IP not allowed:forbidden
                if ($this->errorMessage) {
                    $response->getBody()->write($this->errorMessage);
                }
                return $response->withStatus(403);
            }
        }

        // If here, not disallowed or not-allowed; continue as normal.
        return $next($request, $response);
    }

    /**
     * Check whether a certain IP is explicitly disallowed.
     *
     * If the disallowed is null or empty, then nothing is ever disallowed (return false).
     *
     * Note: this method only performs an exact string match on IP address, no IP masking / range features.
     *
     * @param string $ip The IP address to check against the disallowed.
     * @return boolean
     */
    private function isIpDisallowed($ip) : bool
    {
        if (empty($this->disallowed)) {
            return false;
        }
        return $this->isIpInRange($ip, $this->disallowed);
    }

    /**
     * Check whether a certain IP is explicitly allowed.
     *
     * If the allowed is null or empty, then all IPs are allowed (return true).
     *
     * Note; This method only performs an exact string match on IP address, no IP masking / range features.
     *
     * @param string $ip The IP address to check against the allowed.
     * @return boolean
     */
    private function isIpAllowed($ip) : bool
    {
        if (empty($this->allowed)) {
            return true;
        }
        return $this->isIpInRange($ip, $this->allowed);
    }

    /**
     * @param string   $ip    The IP to check.
     * @param string[] $cidrs The array of IPs/CIDRs to validate against. "/32" netmask is assumed.
     * @return boolean
     */
    private function isIpInRange(string $ip, array $cidrs) : bool
    {
        foreach ($cidrs as $range) {
            if (strpos($range, '/') === false) {
                $range .= '/32';
            }
            // $range is in IP/CIDR format eg 127.0.0.1/24
            list($subnet, $netmask) = explode('/', $range, 2);
            $netmask = ~(pow(2, (32 - (int)$netmask)) - 1);
            if ((ip2long($ip) & $netmask) == (ip2long($subnet) & $netmask)) {
                return true;
            }
        }
        // Nothing matched
        return false;
    }

    /**
     * @return string
     */
    private function getClientIp() : string
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        }
        return '';
    }
}
