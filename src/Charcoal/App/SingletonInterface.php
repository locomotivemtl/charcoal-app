<?php

namespace Charcoal\App;

/**
 * Defines instance as a singleton.
 */
interface SingletonInterface
{
    /**
     * Getter for creating/returning the unique instance of this class
     *
     * @param  mixed $param,...
     * @return object
     */
    public static function instance();
}
