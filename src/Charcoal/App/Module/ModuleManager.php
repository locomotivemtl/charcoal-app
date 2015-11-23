<?php

namespace Charcoal\App\Module;

// Local namespace dependencies
use \Charcoal\App\AbstractManager;

class ModuleManager extends AbstractManager
{
    /**
    * @var array $modules
    */
    private $modules = [];

    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
    * @param array $modules
    * @return ModuleManager Chainable
    */
    public function set_modules(array $modules)
    {
        foreach ($modules as $module_ident => $module_config) {
            $this->add_module($module_ident, $module_config);
        }
        return $this;
    }

    /**
    * @param string $module_ident
    * @param array|ConfigInterface $module_config
    * @return ModuleManager Chainable
    */
    public function add_module($module_ident, $module_config)
    {
        $this->modules[$module_ident] = $module_config;
        return $this;
    }

    /**
    * @return void
    */
    public function setup_modules()
    {
        $modules = $this->config();
        foreach ($modules as $module_ident => $module_config) {
            $module = $this->build_module($module_ident, $module_config);
            $module->setup();
        }
    }

    public function build_module($module)
    {
    }
}
