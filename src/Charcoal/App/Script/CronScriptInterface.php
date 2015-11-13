<?php

namespace Charcoal\App\Script;

/**
*
*/
interface CronScriptInterface
{
    /**
    * @param boolean $use_lock
    * @return CronScriptInterface Chainable
    */
    public function set_use_lock($use_lock);

    /**
    * @return boolean
    */
    public function use_lock();

    /**
    * @throws Exception
    * @return boolean
    */
    public function start_lock();

    /**
    *
    */
    public function stop_lock();
}
