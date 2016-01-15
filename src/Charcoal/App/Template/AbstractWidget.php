<?php

namespace Charcoal\App\Template;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
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

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @param array|ContainerInterface $data Optional dependencies.
     */
    final public function __construct($data = null)
    {
        $this->setLogger($data['logger']);
    }

    /**
     * @param array $data The data array (as [key=>value] pair) to set.
     * @return AbstractWidget Chainable
     */
    public function setData(array $data)
    {
        foreach ($data as $prop => $val) {

            if ($val === null) {
                continue;
            }

            $func = [$this, $this->setter($prop)];
            if (is_callable($func)) {
                $func($val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }
        return $this;
    }

    /**
     * @param boolean $active The active flag.
     * @return AbstractWidget Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
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
    public function createView(array $data = null)
    {
        $view = new GenericView([
            'logger'=>$this->logger
        ]);
        if ($data !== null) {
            $view->setData($data);
        }
        return $view;
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    private function getter($key)
    {
        $getter = $key;
        return $this->camelize($getter);
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    private function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);

    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelCase string.
     */
    private function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}