<?php

namespace Charcoal\Tests\App\Config;

use \Charcoal\App\Config\CacheConfig;

/**
 *
 */
class CacheConfigTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new CacheConfig();
    }

    public function testDefaults()
    {
        $this->assertEquals(['memory'], $this->obj->types());
        $this->assertEquals(864000, $this->obj->defaultTtl());
        $this->assertEquals('charcoal', $this->obj->prefix());
    }

    public function testSetTypes()
    {
        $ret = $this->obj->setTypes(['memcache', 'noop']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['memcache', 'noop'], $this->obj->types());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setTypes([false]);
    }

    public function testAddType()
    {
        $this->assertEquals(['memory'], $this->obj->types());
        $ret = $this->obj->addType('memcache');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['memory', 'memcache'], $this->obj->types());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->addType('foobar');
    }

    public function testSetDefaultTtl()
    {
        $ret = $this->obj->setDefaultTtl(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->defaultTtl());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setDefaultTtl('foo');
    }

    public function testSetPrefix()
    {
        $ret = $this->obj->setPrefix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->prefix());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setPrefix(false);
    }
}
