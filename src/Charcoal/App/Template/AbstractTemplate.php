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

    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->set_logger($data['logger']);
        }

        $this->set_app($data['app']);
    }

    /**
    * @param SlimApp $app
    * @return App Chainable
    */
    public function set_app(SlimApp $app)
    {
        $this->app = $app;
        return $this;
    }

    public function app($app)
    {
        return $this->app;
    }

    /**
    * > LoggerAwareInterface > setLogger()
    *
    * Fulfills the PSR-1 style LoggerAwareInterface
    *
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function setLogger(LoggerInterface $logger)
    {
        return $this->set_logger($logger);
    }

    /**
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function set_logger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
    * @erturn LoggerInterface
    */
    public function logger()
    {
        return $this->logger;
    }

    /**
    * The default Template View is a simple GenericView.
    *
    * @return \Charcoal\View\ViewInterface
    */
    public function create_view(array $data = null)
    {
        $view = new GenericView([
            'logger'=>null
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
