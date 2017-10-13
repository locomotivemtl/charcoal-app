<?php

namespace Charcoal\Tests\App\Config;

use InvalidArgumentException;

// From 'charcoal-app'
use Charcoal\App\Config\CacheConfig;

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
        $this->assertEquals('charcoal', CacheConfig::DEFAULT_NAMESPACE);
        $this->assertEquals((60 * 60), CacheConfig::HOUR_IN_SECONDS);
        $this->assertEquals((60 * 60 * 24), CacheConfig::DAY_IN_SECONDS);
        $this->assertEquals((60 * 60 * 24 * 7), CacheConfig::WEEK_IN_SECONDS);

        $this->assertTrue($this->obj->active());
        $this->assertEquals([ 'memory' ], $this->obj->types());
        $this->assertEquals(CacheConfig::WEEK_IN_SECONDS, $this->obj->defaultTtl());
        $this->assertEquals(CacheConfig::DEFAULT_NAMESPACE, $this->obj->prefix());
    }

    public function testSetActive()
    {
        $ret = $this->obj->setActive(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->active());
    }

    public function testSetTypes()
    {
        $ret = $this->obj->setTypes([ 'memcache', 'noop' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'memcache', 'noop' ], $this->obj->types());

        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setTypes([ false ]);
    }

    public function testAddType()
    {
        $this->assertEquals([ 'memory' ], $this->obj->types());
        $ret = $this->obj->addType('memcache');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'memory', 'memcache' ], $this->obj->types());

        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->addType('foobar');
    }

    public function testSetDefaultTtl()
    {
        $ret = $this->obj->setDefaultTtl(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->defaultTtl());

        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setDefaultTtl('foo');
    }

    public function testSetPrefix()
    {
        $ret = $this->obj->setPrefix('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->prefix());

        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj->setPrefix(false);
    }
}
