<?php

namespace Charcoal\Tests\App\Route;

class ModuleFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new \Charcoal\App\Route\RouteFactory();
    }

    public function testBaseClass()
    {
        $this->assertEquals('\Charcoal\App\Route\RouteInterface', $this->obj->baseClass());
    }

    public function testResolverSuffix()
    {
        $this->assertEquals('Route', $this->obj->resolverSuffix());
    }

    public function testResolve()
    {
        $this->assertEquals('\Foo\BarBazRoute', $this->obj->resolve('foo/bar-baz'));
    }
}
