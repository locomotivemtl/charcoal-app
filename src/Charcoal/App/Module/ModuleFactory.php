<?php

namespace \Charcoal\App\Module;

class ModuleFactory
{
    /**
    * Class Resolver to map identifier to a class name.
    *
    * @var mixed $class_resolver
    */
    private $class_resolver;

    public function __construct($container)
    {
        $this->class_resolver = $container->get('class_resolver');
    }

    public function create_module($type, $config, $app)
    {
//        $class_name = $this->class_resolver->resolve($type);
//        $type =
    }

    public function create_module_config($config)
    {

    }
}
