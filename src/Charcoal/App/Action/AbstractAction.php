<?php

namespace Charcoal\App\Action;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Charcoal;

use \Charcoal\Action\ActionInterface;

/**
* Default implementation, as abstract class, of `ActionInterface`
*/
abstract class AbstractAction implements ActionInterface
{
    const MODE_JSON = 'json';
    const MODE_REDIRECT = 'redirect';
    const MODE_BOOLEAN = 'boolean';
    const MODE_OUTPUT = 'output';
    const DEFAULT_MODE = self::MODE_REDIRECT;

    /**
    * @var string $mode
    */
    private $mode = self::DEFAULT_MODE;

    /**
    * @var boolean $success
    */
    private $success = false;


    /**
    * @param array $data
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
    * @param string $mode
    * @throws InvalidArgumentException if mode is not a string
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
    * @param bool $success
    * @throws InvalidArgumentException if success is not a boolean
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
    * @return bool
    */
    public function success()
    {
        return $this->success;
    }

    /**
    * @return string
    */
    abstract public function response();
}
