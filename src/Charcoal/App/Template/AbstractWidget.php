<?php

namespace Charcoal\App\Template;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// Module `charcoal-view` dependencies
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Local namespace dependencies
use \Charcoal\App\Template\WidgetInterface;

/**
*
*/
abstract class AbstractWidget implements
    WidgetInterface,
    //DescribableInterface,
    LoggerAwareInterface,
    ViewableInterface
{
    //use DescribableTrait;
    use ViewableTrait;

    /**
    * @var LoggerInterface $logger
    */
    private $logger;

    /**
    * @var boolean $active
    */
    private $active;

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->set_logger($data['logger']);
        }
    }

    /**
    * > LoggerAwareInterface > setLogger()
    *
    * Fulfills the PSR-1 / PSR-3 style LoggerAwareInterface
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
    * @param array $data
    * @return AbstractWidget Chainable
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
    * @param boolean $active
    * @throws InvalidArgumentException
    * @return AbstractWidget Chainable
    */
    public function set_active($active)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException(
                'Active must be a boolean'
            );
        }
        $this->active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return $this->active;
    }

    /**
    * ViewableInterface > create_view().
    *
    * @param array $data Optional
    * @return ViewInterface
    */
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
