<?php

namespace Charcoal\App;

use RuntimeException;

// From Slim
use Slim\Interfaces\CallableResolverInterface;

/**
 * Resolve Callable
 *
 * This trait enables the resolution of 'class:method' strings into a closure.
 * This class is based on {@see \Slim\CallableResolverAwareTrait Slim's trait}.
 *
 * The resolver will attempt checks on the current class, an optional alternate
 * object, the DI container.
 *
 * Can resolve the following string formats:
 *
 * - 'class:method'
 * - 'parent:method'
 * - 'static:method'
 * - 'self:method' or ':method'
 */
trait CallableResolverAwareTrait
{
    /**
     * The cache of string-based callables.
     *
     * @var array
     */
    protected static $resolvedCallableCache = [];

    /**
     * A regular expression for matching 'class:method' callable strings.
     *
     * @var string
     */
    protected $callablePattern = '!^([^\:]+)?\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

    /**
     * Store the callable resolver.
     *
     * @var CallableResolverInterface
     */
    protected $callableResolver;

    /**
     * Set a callable resolver.
     *
     * @param  CallableResolverInterface $resolver A callable resolver.
     * @return self
     */
    protected function setCallableResolver(CallableResolverInterface $resolver)
    {
        $this->callableResolver = $resolver;

        return $this;
    }

    /**
     * Resolve a string of the format 'class:method' into a closure that the
     * router can dispatch.
     *
     * @param  callable|string $callable A callable function or method, either as a reference or string.
     * @param  object|null     $context  Optional. An additional context under to test $callable as a method.
     * @throws RuntimeException If the string cannot be resolved as a callable
     *     or the resolver was not previously set.
     * @return \Closure
     */
    protected function resolveCallable($callable, $context = null)
    {
        if (!isset($this->callableResolver)) {
            throw new RuntimeException(
                sprintf('Callable Resolver is not defined for "%s"', get_class($this))
            );
        }

        if (!is_callable($callable) && is_string($callable)) {
            $key = $callable;

            if (isset(static::$resolvedCallableCache[$key])) {
                return static::$resolvedCallableCache[$key];
            }

            if (preg_match($this->callablePattern, $callable, $matches)) {
                $class  = $matches[1];
                $method = $matches[2];

                if (is_object($context)) {
                    $callable = [ $context, $method ];
                }

                if (!is_callable($callable)) {
                    switch ($class) {
                        case '':
                        case 'self':
                            $callable = [ $this, $method ];
                            break;

                        case 'static':
                            $callable = [ static::class, $method ];
                            break;

                        case 'parent':
                            $callable = [ $this, 'parent::'.$method ];
                            break;
                    }
                }
            }

            if (is_callable($callable)) {
                static::$resolvedCallableCache[$key] = $callable;
            }
        }

        return $this->callableResolver->resolve($callable);
    }
}
