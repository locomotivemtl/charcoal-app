<?php

namespace Charcoal\App\Action;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

/**
 *
 */
interface ActionInterface
{
    /**
     * Actions are callable, with http request and response as parameters.
     *
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response);

    /**
     * @param array $data The data to set.
     * @return ActionInterface Chainable
     */
    public function setData(array $data);

    /**
     * @param string $mode The action mode.
     * @return ActionInterface Chainable
     */
    public function setMode($mode);

    /**
     * @return string
     */
    public function mode();

    /**
     * @param boolean $success Success flag (true / false).
     * @return ActionInterface Chainable
     */
    public function setSuccess($success);

    /**
     * @return boolean
     */
    public function success();

    /**
     * @param string $url The success URL.
     * @return ActionInterface Chainable
     */
    public function setSuccessUrl($url);

    /**
     * @return string
     */
    public function successUrl();

    /**
     * @param string $url The success URL.
     * @return ActionInterface Chainable
     */
    public function setFailureUrl($url);

    /**
     * @return string
     */
    public function failureUrl();

    /**
     * @return string
     */
    public function redirectUrl();

    /**
     * @return array
     */
    public function results();

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response);

    /**
     * Initialize the action with a request.
     *
     * @param RequestInterface $request The request to initialize.
     * @return boolean Success / Failure.
     */
    public function init(RequestInterface $request);
}
