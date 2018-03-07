<?php

namespace Charcoal\App\Support;

use Charcoal\App\AppConfig;

/**
 * Mixin for objects that depend on the application configset.
 */
trait ConfigAwareTrait
{
    /**
     * Store a reference to the application configuration.
     *
     * @var AppConfig
     */
    private $appConfig;

    /**
     * Set the application's configset.
     *
     * @param  AppConfig $config A configset.
     * @return void
     */
    protected function setAppConfig(AppConfig $config)
    {
        $this->appConfig = $config;
    }

    /**
     * Get the application configset or a specific setting.
     *
     * @param  string|null $key     Optional data key to retrieve from the configset.
     * @param  mixed|null  $default Optional default value to return if $key is given
     *     but a value does not exist.
     * @return mixed|AppConfig If $key is NULL, the configset is returned.
     *     If $key is given and a value is present in the configset, the key's value is returned.
     *     If $key is given but a value is missing in the configset, the value of $default is returned.
     *     If $default is a {@see \Closure}, it's invoked first and its value is returned.
     */
    protected function getAppConfig($key = null, $default = null)
    {
        if ($key) {
            if (isset($this->appConfig[$key])) {
                return $this->appConfig[$key];
            } else {
                if (!is_string($default) && is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            }
        }

        return $this->appConfig;
    }
}
