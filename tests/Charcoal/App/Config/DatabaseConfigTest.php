<?php

namespace Charcoal\Tests\App\Config;

use \Charcoal\App\Config\DatabaseConfig;

/**
 *
 */
class DatabaseConfigTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
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
}
