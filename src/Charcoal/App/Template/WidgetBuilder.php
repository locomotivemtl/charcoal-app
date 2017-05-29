<?php

namespace Charcoal\App\Template;

use InvalidArgumentException;

// From PSR-11
use Psr\Container\ContainerInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

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
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @param FactoryInterface   $factory   An object factory.
     * @param ContainerInterface $container A service container.
     */
    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * @param  array|\ArrayAccess $options The form group build options / config.
     * @throws InvalidArgumentException If the widget is invalid.
     * @return WidgetInterface The "built" widget object.
     */
    public function build($options)
    {
        if (isset($options['controller'])) {
            $objType = $options['controller'];
        } elseif (isset($options['type'])) {
            $objType = $options['type'];
        } else {
            throw new InvalidArgumentException('Undefined widget type');
        }

        $obj = $this->factory->create($objType);
        $obj->setData($options);

        return $obj;
    }
}
