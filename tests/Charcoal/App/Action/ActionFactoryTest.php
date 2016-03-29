<?php

namespace Charcoal\Tests\App\Action;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new \Charcoal\App\Action\ActionFactory();
    }

    public function testBaseClass()
    {
        $this->assertEquals('\Charcoal\App\Action\ActionInterface', $this->obj->baseClass());
    }

    public function testResolverSuffix()
    {
        $this->assertEquals('Action', $this->obj->resolverSuffix());
    }

    public function testResolve()
    {
        $this->assertEquals('\Foo\BarBazAction', $this->obj->resolve('foo/bar-baz'));
    }
}
