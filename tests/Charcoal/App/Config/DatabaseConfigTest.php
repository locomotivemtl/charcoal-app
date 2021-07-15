<?php

namespace Charcoal\Tests\App\Config;

use Charcoal\App\Config\DatabaseConfig;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class DatabaseConfigTest extends AbstractTestCase
{
    public $obj;

    public function setUp(): void
    {
        $this->obj = new DatabaseConfig();
    }

    public function testDefaults()
    {
        $this->assertEquals('mysql', $this->obj->type());
        $this->assertEquals('localhost', $this->obj->hostname());
        $this->assertEquals('', $this->obj->username());
        $this->assertEquals('', $this->obj->database());
        $this->assertFalse($this->obj->disableUtf8());
    }

    public function testSetType()
    {
        $ret = $this->obj->setType('sqlite');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('sqlite', $this->obj->type());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setType([]);
    }

    public function testSetHostname()
    {
        $ret = $this->obj->setHostname('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->hostname());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setHostname([]);
    }

    public function testSetUsername()
    {
        $ret = $this->obj->setUsername('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->username());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setUsername([]);
    }

    public function testSetPassword()
    {
        $ret = $this->obj->setPassword('baz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('baz', $this->obj->password());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setPassword([]);
    }

    public function testSetDatabase()
    {
        $ret = $this->obj->setDatabase('barbaz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('barbaz', $this->obj->database());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setDatabase([]);
    }

    public function testSetDIsableUtf8()
    {
        $ret = $this->obj->setDIsableUtf8(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->disableUtf8());

        $this->obj['disable_utf8'] = 0;
        $this->assertFalse($this->obj['disable_utf8']);
    }
}
