<?php

namespace Charcoal\App\Template;

use InvalidArgumentException;

// From Pimple
use Pimple\Container;

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
