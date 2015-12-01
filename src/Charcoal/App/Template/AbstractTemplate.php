<?php

namespace Charcoal\App\Template;

// slim/slim dependencies
use \Slim\App as SlimApp;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Local namespace dependencies
use \Charcoal\App\Template\TemplateInterface;

/**
 *
 */
abstract class AbstractTemplate implements
    LoggerAwareInterface,
    TemplateInterface,
    ViewableInterface
{

    use ViewableTrait;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var SlimApp $app
     */
    private $app;

    /**
     * @param array $data The dependencies (app and logger).
     */
    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->set_logger($data['logger']);
        }

        $this->set_app($data['app']);
    }

    /**
     * @param SlimApp $app The Slim app instance.
     * @return App Chainable
     */
    public function set_app(SlimApp $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return SlimApp
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * > LoggerAwareInterface > setLogger()
     *
     * Fulfills the PSR-1 style LoggerAwareInterface
     *
     * @param LoggerInterface $logger A PSR-3 compatible logger instance.
     * @return AbstractEngine Chainable
     */
    public function setLogger(LoggerInterface $logger)
    {
        return $this->set_logger($logger);
    }

    /**
     * @param LoggerInterface $logger A PSR-3 compatible logger instance.
     * @return AbstractEngine Chainable
     */
    public function set_logger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * The default Template View is a simple GenericView.
     *
     * @param array $data The data array (as [key=>value] pair) to set.
     * @return AbtractTemplate Chainable
     */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
            } else {
                $this->{$prop} = $val;
            }
        }

        // Chainable
        return $this;
    }

    /**
     * The default Template View is a simple GenericView.
     *
     * @param array $data The optional view data.
     * @return \Charcoal\View\ViewInterface
     */
    public function create_view(array $data = null)
    {
        $view = new GenericView([
            'logger'=>$this->logger()
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
