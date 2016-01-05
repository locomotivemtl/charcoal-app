<?php

namespace Charcoal\App\Template;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

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
    LoggerAwareInterface,
    ViewableInterface
{
    use LoggerAwareTrait;
    use ViewableTrait;
    use LoggerAwareTrait;

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @param array $data Optional dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }
    }

    /**
     * @param array $data The data array (as [key=>value] pair) to set.
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
     * @param boolean $active The active flag.
     * @throws InvalidArgumentException If the active parameter is not a boolean.
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
     * @param array $data Optional view data.
     * @return ViewInterface
     */
    public function create_view(array $data = null)
    {
        $view = new \Charcoal\View\GenericView([
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
