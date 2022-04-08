<?php

namespace Charcoal\App\Action;

use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From Pimple
use Pimple\Container;

// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;

// From 'charcoal-app'
use Charcoal\App\Helper\CallbackStream;
use Charcoal\App\Action\ActionInterface;

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
abstract class AbstractAction extends AbstractEntity implements
    ActionInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    const MODE_JSON = 'json';
    const MODE_XML = 'xml';
    const MODE_REDIRECT = 'redirect';
    const MODE_EVENT_STREAM = 'event-stream';
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
     * @var string|null $successUrl
     */
    private $successUrl;

    /**
     * @var string|null $failureUrl
     */
    private $failureUrl;

    /**
     * @param array|\ArrayAccess $data The dependencies (app and logger).
     */
    public function __construct($data = null)
    {
        $this->setLogger($data['logger']);

        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * Initialize the action with a request.
     *
     * @param RequestInterface $request The request to initialize.
     * @return boolean Success / Failure.
     */
    public function init(RequestInterface $request)
    {
        // This method is a stub. Reimplement in children methods to ensure template initialization.
        return true;
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
                $response->getBody()->write(json_encode($this->results()));
                $response = $response->withHeader('Content-Type', 'application/json');
                break;

            case self::MODE_XML:
                $response->getBody()->write($this->results());
                $response = $response->withHeader('Content-Type', 'text/xml');
                break;

            case self::MODE_REDIRECT:
                $response = $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->redirectUrl());
                break;

            case self::MODE_EVENT_STREAM:
                $output = new CallbackStream(function () {
                    return $this->results();
                });

                $response = $response
                    ->withHeader('Content-Type', 'text/event-stream')
                    ->withHeader('Cache-Control', 'no-cache')
                    ->withBody($output);
                break;
        }

        return $response;
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
     * @return self
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
     * @return array|mixed
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

    /**
     * Give an opportunity to children classes to inject dependencies from a Pimple Container.
     *
     * Does nothing by default, reimplement in children classes.
     *
     * The `$container` DI-container (from `Pimple`) should not be saved or passed around, only to be used to
     * inject dependencies (typically via setters).
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children action classes.
    }
}
