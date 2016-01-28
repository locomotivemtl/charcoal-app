<?php

namespace Charcoal\App\Module;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;

/**
 *
 */
class ModuleManager extends AbstractManager
{
    /**
     * @var array $modules
     */
    private $modules = [];

    /**
     * @param array $modules The list of modules to add.
     * @return ModuleManager Chainable
     */
    public function setModules(array $modules)
    {
        foreach ($modules as $module_ident => $module_config) {
            $this->addModule($module_ident, $module_config);
        }
        return $this;
    }

    /**
     * @param string                $module_ident  The module identifier.
     * @param array|ConfigInterface $module_config The module configuration data.
     * @return ModuleManager Chainable
     */
    public function addModule($module_ident, array $module_config)
    {
        $this->modules[$module_ident] = $module_config;
        return $this;
    }

    /**
     * @return void
     */
    public function setupModules()
    {
        $modules = $this->config();
        $module_factory = new ModuleFactory();
        foreach ($modules as $module_ident => $module_config) {
            $module = $module_factory->create($module_ident, [
                'app'    => $this->app(),
                'logger' => $this->logger
            ]);
            // Merge custom data to config
            $module->config()->merge($module_config);
            $module->setup();
        }
    }
}
