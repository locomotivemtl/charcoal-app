<?php

namespace Charcoal\App\Script;

/**
 *
 */
interface CronScriptInterface
{
    /**
     * @param boolean $useLock The boolean flag if a lock should be used.
     * @return self
     */
    public function setUseLock($useLock);

    /**
     * @return boolean
     */
    public function useLock();

    /**
     * @return boolean
     */
    public function startLock();

    /**
     * @return void
     */
    public function stopLock();
}
