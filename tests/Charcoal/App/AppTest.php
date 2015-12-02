<?php

namespace Charcoal\Tests\App;

use \Charcoal\App\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $GLOBALS['app'];
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\App\App', $obj);
    }
}
