<?php

namespace Charcoal\App\Module;

class ModuleManager
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
    * # Required dependencies
    * - `modules`
    * - `app`
    *
    * @param array Constructore depenencies
    */
    public function __construct($data)
    {
        $this->modules = $data['modules'];
        $this->app = $data['app'];
    }

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
        $this->_modules[$module_ident] = $module_config;
        return $this;
    }

    /**
    * @return void
    */
    public function setup_modules()
    {
        foreach ($this->_modules as $module_ident => $module_config) {
            $module = $this->build_module($module_ident, $module_config);
            $module->setup();
        }
    }

    public function build_module($module)
    {

    }
}
