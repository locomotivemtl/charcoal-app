<?php

namespace Charcoal\App;

use \LogicException;
use \ReflectionClass;
use \ReflectionException;

/**
 * There can only be one.
 *
 * Limit classes to only one instance.
 *
 * A simple implementation of `SingletonInterface`.
 */
trait SingletonTrait
{
    /**
     * Store many unique instances
     *
     * @var object[] $instance
     */
    protected static $instance = [];

    /**
     * @throws LogicException if trying to clone class
     */
    final private function __clone()
    {
        throw new LogicException(
            sprintf(
                'Cloning "%s" is not allowed.',
                get_called_class()
            )
        );
    }

    /**
     * @throws LogicException if trying to unserialize class
     */
    final private function __wakeup()
    {
        throw new LogicException(
            sprintf(
                'Unserializing "%s" is not allowed.',
                get_called_class()
            )
        );
    }

    /**
     * Getter for creating/returning the unique instance of this class
     *
     * @param  mixed $param,...
     * @return self
     */
    public static function instance()
    {
        $called_class = get_called_class();

        if (!isset(static::$instance[$called_class])) {
            if (func_num_args()) {
                $reflected_class = new ReflectionClass($called_class);
                $class_instance  = $reflected_class->newInstanceArgs(func_get_args());
                // $class_instance = new $called_class(...func_get_args());
            } else {
                $class_instance = new $called_class();
            }
            static::$instance[$called_class] = $class_instance;
        }

        return static::$instance[$called_class];
    }
}
