<?php

namespace Charcoal\Tests\App\Action;

use \Charcoal\App\App;

class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    public $app;
    public $obj;

    public function setUp()
    {
        $this->app = $GLOBALS['app'];
        $container = $this->app->getContainer();
        $this->obj = $this->getMockForAbstractClass('\Charcoal\App\Action\AbstractAction', [[
            'app'=>$this->app,
            'logger'=>$container['logger']
        ]]);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\App\Action\AbstractAction', $obj);
    }

    public function testSetMode()
    {
        $this->assertEquals('json', $this->obj->mode());
        $ret = $this->obj->setMode('redirect');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('redirect', $this->obj->mode());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMode(false);
    }

    public function testSetSuccess()
    {
        $ret = $this->obj->setSuccess(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->success());
        $this->obj->setSuccess(true);
        $this->assertTrue($this->obj->success());

        $this->obj->setSuccess("1");
        $this->assertTrue($this->obj->success());
    }

    public function testSuccessUrl()
    {
        $this->assertEquals('', $this->obj->successUrl());
        $ret = $this->obj->setSuccessUrl('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->successUrl());
    }
}
