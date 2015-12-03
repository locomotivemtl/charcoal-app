<?php

namespace Charcoal\App\Action;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

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
     * @return AbstractAction Chainable
     */
    public function set_data(array $data);

    /**
     * @param string $lang The langauge code for current action.
     * @return ActionInterface Chainable
     */
    public function set_lang($lang);

    /**
     * @return string
     */
    public function lang();

    /**
     * @param string $mode The action mode.
     * @return ActionInterface Chainable
     */
    public function set_mode($mode);

    /**
     * @return string
     */
    public function mode();

    /**
     * @param boolean $success Success flag (true / false).
     * @return ActionInterface Chainable
     */
    public function set_success($success);

    /**
     * @return boolean
     */
    public function success();

    /**
     * @param mixed $url The success URL (translation string).
     * @return ActionInterface Chainable
     */
    public function set_success_url($url);

    /**
     * @return TranslationString
     */
    public function success_url();

    /**
     * @param mixed $url The success URL (translation string).
     * @return ActionInterface Chainable
     */
    public function set_failure_url($url);

    /**
     * @return TranslationString
     */
    public function failure_url();

    /**
     * @return string
     */
    public function redirect_url();

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
}
