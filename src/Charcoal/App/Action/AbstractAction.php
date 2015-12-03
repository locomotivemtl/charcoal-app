<?php

namespace Charcoal\App\Action;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Module `charcoal-core` dependencies
use \Charcoal\Translation\TranslationString;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\App;
use \Charcoal\App\Action\ActionInterface;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;

/**
 * Default implementation, as abstract class, of `ActionInterface`
 */
abstract class AbstractAction implements
    ActionInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    const MODE_JSON = 'json';
    const MODE_REDIRECT = 'redirect';
    const DEFAULT_MODE = self::MODE_JSON;
    
    /**
     * @var App
     */
    private $app;

    /**
     * @var string $lang
     */
    private $lang;

    /**
     * @var string $mode
     */
    private $mode = self::DEFAULT_MODE;

    /**
     * @var boolean $success
     */
    private $success = false;

    /**
     * @var TranslationString $success_url
     */
    private $success_url;

    /**
     * @var TranslationString $failure_url
     */
    private $failure_url;

    /**
     * @param array $data The dependencies (app and logger).
     */
    public function __construct(array $data = null)
    {
        $this->set_logger($data['logger']);
        $this->set_app($data['app']);
    }
    
    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
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
                    ->withHeader('Location', $this->redirect_url());
                break;
        }

        return $response;
    }

    /**
     * @param App $app The template's parent charcoal app instance.
     * @return App Chainable
     */
    public function set_app(App $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return App
     */
    public function app()
    {
        return $this->app;
    }

    
    /**
     * @param array $data The data to set.
     * @return AbstractAction Chainable
     */
    public function set_data(array $data)
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
     * @param string $lang The langauge code for current action.
     * @throws InvalidArgumentException If the lang parameter is not a string.
     * @return ActionInterface Chainable
     */
    public function set_lang($lang)
    {
        if (!is_string($lang)) {
            throw new InvalidArgumentException(
                'Can not set language, lang parameter needs to be a string.'
            );
        }
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return string
     */
    public function lang()
    {
        return $this->lang;
    }

    /**
     * @param string $mode The action mode.
     * @throws InvalidArgumentException If the mode argument is not a string.
     * @return ActionInterface Chainable
     */
    public function set_mode($mode)
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
    public function set_success($success)
    {
        if (!is_bool($success)) {
            throw new InvalidArgumentException(
                'Success needs to be a boolean'
            );
        }
        $this->success = $success;
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
     * @param mixed $url The success URL (translation string).
     * @return ActionInterface Chainable
     */
    public function set_success_url($url)
    {
        $this->success_url = new TranslationString($url);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function success_url()
    {
        return $this->success_url;
    }

    /**
     * @param mixed $url The success URL (translation string).
     * @return ActionInterface Chainable
     */
    public function set_failure_url($url)
    {
        $this->failure_url = new TranslationString($url);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function failure_url()
    {
        return $this->failure_url;
    }

    /**
     * @return string
     */
    public function redirect_url()
    {
        if ($this->success() === true) {
            $url = $this->success_url();
        } else {
            $url = $this->failure_url();
        }

        // Get the translated URL
        $url = $url->val($this->lang());
        if (!$url) {
            $url = '';
        }
        return $url;
    }

    /**
     * @return array
     */
    abstract public function results();

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    abstract public function run(RequestInterface $request, ResponseInterface $response);
}
