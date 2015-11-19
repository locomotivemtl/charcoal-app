<?php

namespace Charcoal\App\Template;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// Module `charcoal-view` dependencies
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

        if ($data !== null) {
            $this->set_data($data);
        }
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


    public function create_view(array $data = null)
    {
        $view = new \Charcoal\View\GenericView([
            'logger'=>null
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
