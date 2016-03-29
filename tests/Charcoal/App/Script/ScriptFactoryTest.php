<?php

namespace Charcoal\Tests\App\Script;

class ModuleFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new \Charcoal\App\Script\ScriptFactory();
    }

    public function testBaseClass()
    {
        $this->assertEquals('\Charcoal\App\Script\ScriptInterface', $this->obj->baseClass());
    }

    public function testResolverSuffix()
    {
        $this->assertEquals('Script', $this->obj->resolverSuffix());
    }

    public function testResolve()
    {
        $this->assertEquals('\Foo\BarBazScript', $this->obj->resolve('foo/bar-baz'));
    }
}
