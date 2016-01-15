<?php

namespace Charcoal\App\Action;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Action\ActionInterface;

/**
 * Default implementation, as abstract class, of  the `ActionInterface`.
 *
 * Actions respond to a (PSR7-style) request and response and returns back the response.
 *
 * Typical implementations only need to implement the following 2 _abstract_ methods:
 *
 * ``` php
 * // Returns an associative array of results
 * public function results();
 * // Gets a psr7 request and response and returns a response
 * public function run(RequestInterface $request, ResponseInterface $response);
 * ```
 * Actions can be invoked (with the magic `__invoke()` method) which automatically call the:
 */
abstract class AbstractAction implements
    ActionInterface,
    AppAwareInterface,
    LoggerAwareInterface
{
    use AppAwareTrait;
    use LoggerAwareTrait;

    const MODE_JSON = 'json';
    const MODE_REDIRECT = 'redirect';
    const DEFAULT_MODE = self::MODE_JSON;

    /**
     * @var string $mode
     */
    private $mode = self::DEFAULT_MODE;

    /**
     * @var boolean $success
     */
    private $success = false;

    /**
     * @var string $successUrl
     */
    private $successUrl;

    /**
     * @var string $failureUrl
     */
    private $failureUrl;

    /**
     * @param array $data The dependencies (app and logger).
     */
    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        $this->setApp($data['app']);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     * @see self::run()
     */
    final public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $response = $this->run($request, $response);

        switch ($this->mode()) {
            case self::MODE_JSON:
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($this->results()));
                break;

            case self::MODE_REDIRECT:
                $response = $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->redirectUrl());
                break;
        }

        return $response;
    }

    /**
     * @param array $data The data to set.
     * @return AbstractAction Chainable
     */
    public function setData(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];

            if ($val === null) {
                continue;
            }

            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }
        return $this;
    }

    /**
     * @param string $mode The action mode.
     * @throws InvalidArgumentException If the mode argument is not a string.
     * @return ActionInterface Chainable
     */
    public function setMode($mode)
    {
        if (!is_string($mode)) {
            throw new InvalidArgumentException(
                'Mode needs to be a string'
            );
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function mode()
    {
        return $this->mode;
    }

    /**
     * @param boolean $success Success flag (true / false).
     * @throws InvalidArgumentException If the success argument is not a boolean.
     * @return ActionInterface Chainable
     */
    public function setSuccess($success)
    {
        $this->success = !!$success;
        return $this;
    }

    /**
     * @return boolean
     */
    public function success()
    {
        return $this->success;
    }

    /**
     * @param string|null $url The success URL.
     * @throws InvalidArgumentException If the URL parameter is not a string.
     * @return ActionInterface Chainable
     */
    public function setSuccessUrl($url)
    {
        if ($url === null) {
            $this->successUrl = null;
            return $this;
        }
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'Success URL must be a string'
            );
        }
        $this->successUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function successUrl()
    {
        if ($this->successUrl === null) {
            return '';
        }
        return $this->successUrl;
    }

    /**
     * @param string|null $url The success URL.
     * @throws InvalidArgumentException If the URL parameter is not a string.
     * @return ActionInterface Chainable
     */
    public function setFailureUrl($url)
    {
        if ($url === null) {
            $this->failureUrl = null;
            return $this;
        }
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'Failure URL must be a string'
            );
        }
        $this->failureUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function failureUrl()
    {
        if ($this->failureUrl === null) {
            return '';
        }
        return $this->failureUrl;
    }

    /**
     * @return string
     */
    public function redirectUrl()
    {
        if ($this->success() === true) {
            $url = $this->successUrl();
        } else {
            $url = $this->failureUrl();
        }

        return $url;
    }

    /**
     * Returns an associative array of results (set after being  invoked / run).
     *
     * The raw array of results will be called from `__invoke()`.
     *
     * @return array
     */
    abstract public function results();

    /**
     * Gets a psr7 request and response and returns a response.
     *
     * Called from `__invoke()` as the first thing.
     *
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    abstract public function run(RequestInterface $request, ResponseInterface $response);
}