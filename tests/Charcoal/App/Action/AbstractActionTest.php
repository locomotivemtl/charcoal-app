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
            'logger'=>$container['logger']
        ]]);
    }

    public function testSetData()
    {
        $ret = $this->obj->setData([
            'mode' => 'redirect',
            'success' => true,
            'success_url' => 'win',
            'failure_url' => 'fail'
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('redirect', $this->obj->mode());
        $this->assertEquals(true, $this->obj->success());
        $this->assertEquals('win', $this->obj->successUrl());
        $this->assertEquals('fail', $this->obj->failureUrl());
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

        $this->assertEquals('', $this->obj->setSuccessUrl(null)->successUrl());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setSuccessUrl([]);
    }

    public function testSetFailureUrl()
    {
        $this->assertEquals('', $this->obj->failureUrl());
        $ret = $this->obj->setFailureUrl('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->failureUrl());

        $this->assertEquals('', $this->obj->setFailureUrl(null)->successUrl());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setFailureUrl([]);
    }

    public function testRedirectUrlSuccess()
    {
        $this->obj->setData([
            'failure_url' => 'fail',
            'success_url' => 'win'
        ]);

        $this->obj->setSuccess(true);
        $this->assertEquals('win', $this->obj->redirectUrl());
        $this->obj->setSuccess(false);
        $this->assertEquals('fail', $this->obj->redirectUrl());
    }

    /**
     * This test assert that the action object is invokable.
     *
     * For this, the `run` method must be added as public in the mock object.
     */
    public function testInvokable()
    {
        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = new \Slim\Http\Response();

        $this->obj->expects($this->any())
            ->method('run')
            ->will($this->returnValue($response));

        $obj = $this->obj;
        $res = $obj($request, $response);
    }
}
