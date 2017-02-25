<?php

namespace Charcoal\Tests\App\Route;

use \Charcoal\App\Route\ActionRouteConfig;

class ActionRouteConfigTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new ActionRouteConfig();
    }

    public function testSetActionData()
    {
        $ret = $this->obj->setActionData([ 'foo' => 'bar' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->actionData());
    }
}
