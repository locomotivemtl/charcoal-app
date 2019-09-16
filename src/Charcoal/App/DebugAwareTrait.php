<?php

namespace Charcoal\App;

/**
 * Provides an application template/action with the ability to determine the debug mode.
 */
trait DebugAwareTrait
{
    /**
     * Whether the debug mode is enabled.
     *
     * @var boolean
     */
    private $debug = false;

    /**
     * Set application debug mode.
     *
     * @param  boolean $debug The debug flag.
     * @return void
     */
    protected function setDebug($debug)
    {
        $this->debug = !!$debug;
    }

    /**
     * Retrieve the application debug mode.
     *
     * @return boolean
     */
    public function debug()
    {
        return $this->debug;
    }
}
