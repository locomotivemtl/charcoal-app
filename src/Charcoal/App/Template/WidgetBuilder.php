<?php

namespace Charcoal\App\Template;

use \Charcoal\Factory\FactoryInterface;
use \Pimple\Container;

/**
 * Build widgets from config, with a WidgetFactory
 */
class WidgetBuilder
{

    /**
     * @var FactoryInterface $factory
     */
    protected $factory;

    /**
     * A Pimple dependency-injection container to fulfill the required services.
     * @var Container $container
     */
    protected $container;

    /**
     * @param FactoryInterface $factory   An object factory.
     * @param Container        $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * @param array|\ArrayAccess $options The form group build options / config.
     * @return WidgetInterface The "built" widget object.
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType, [
            'logger'    =>  $container['logger'],
            'view'      =>  $container['view']
        ]);
        $obj->setDependencies($container);
        $obj->setData($options);
        return $obj;
    }
}
