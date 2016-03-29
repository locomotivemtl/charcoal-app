<?php

namespace Charcoal\Tests\App\Template;

class ModuleFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new \Charcoal\App\Template\TemplateFactory();
    }

    public function testBaseClass()
    {
        $this->assertEquals('\Charcoal\App\Template\TemplateInterface', $this->obj->baseClass());
    }

    public function testResolverSuffix()
    {
        $this->assertEquals('Template', $this->obj->resolverSuffix());
    }

    public function testResolve()
    {
        $this->assertEquals('\Foo\BarBazTemplate', $this->obj->resolve('foo/bar-baz'));
    }
}
