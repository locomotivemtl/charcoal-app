<?php

namespace Charcoal\Tests\App\Module;

class ModuleFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new \Charcoal\App\Module\ModuleFactory();
    }

    public function testBaseClass()
    {
        $this->assertEquals('\Charcoal\App\Module\ModuleInterface', $this->obj->baseClass());
    }

    public function testResolverSuffix()
    {
        $this->assertEquals('Module', $this->obj->resolverSuffix());
    }

    public function testResolve()
    {
        $this->assertEquals('\Foo\BarBazModule', $this->obj->resolve('foo/bar-baz'));
    }
}
