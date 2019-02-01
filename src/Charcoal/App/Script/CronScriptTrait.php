<?php

namespace Charcoal\App\Script;

use Exception;

/**
 * Cron-job utilities
 */
trait CronScriptTrait
{
    /**
     * @var boolean $useLock
     */
    private $useLock = false;

    /**
     * Lock file pointer
     * @var resource $lockFilePointer
     */
    private $lockFilePointer;

    /**
     * @param boolean $useLock The boolean flag if a lock should be used.
     * @return self
     */
    public function setUseLock($useLock)
    {
        $this->useLock = !!$useLock;
        return $this;
    }

    /**
     * @return boolean
     */
    public function useLock()
    {
        return $this->useLock;
    }

    /**
     * @throws Exception If the lock file can not be opened or the script is already locked.
     * @return boolean
     */
    public function startLock()
    {
        $lockFile = $this->getLockFileName();
        $this->lockFilePointer = fopen($lockFile, 'w');
        if (!$this->lockFilePointer) {
            throw new Exception(
                sprintf('Can not run action. Lock file not available: "%s"', $lockFile)
            );
        }
        if (flock($this->lockFilePointer, LOCK_EX)) {
            return true;
        } else {
            throw new Exception(
                sprintf('Can not run action. Action locked: "%s".', $lockFile)
            );
        }
    }

    /**
     * @return void
     */
    public function stopLock()
    {
        if ($this->lockFilePointer) {
            flock($this->lockFilePointer, LOCK_UN);
            fclose($this->lockFilePointer);
        }
    }

    /**
     * @return string
     */
    private function getLockFileName()
    {
        $lockName = str_replace('\\', '-', static::class);
        $lockName .= md5(__DIR__);

        return sys_get_temp_dir().DIRECTORY_SEPARATOR.$lockName;
    }
}
