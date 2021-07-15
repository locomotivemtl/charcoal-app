<?php

namespace Charcoal\Tests\App\Route;

use Charcoal\App\Route\ActionRouteConfig;
use Charcoal\Tests\AbstractTestCase;

class ActionRouteConfigTest extends AbstractTestCase
{
    public $obj;

    public function setUp(): void
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
