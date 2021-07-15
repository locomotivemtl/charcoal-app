<?php

namespace Charcoal\Tests\App\Action;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Slim
use Slim\Http\Response;

// From Pimple
use Pimple\Container;

// From 'charcoal-app'
use Charcoal\App\Action\AbstractAction;
use Charcoal\Tests\App\ContainerProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractActionTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var AbstractAction
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = $this->getMockForAbstractClass(AbstractAction::class, [[
            'logger'    => $container['logger'],
            'container' => $container
        ]]);
    }

    public function testSetData()
    {
        $ret = $this->obj->setData([
            'mode'        => 'redirect',
            'success'     => true,
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

        $this->expectException('\InvalidArgumentException');
        $this->obj->setMode(false);
    }

    public function testSetSuccess()
    {
        $ret = $this->obj->setSuccess(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->success());
        $this->obj->setSuccess(true);
        $this->assertTrue($this->obj->success());

        $this->obj->setSuccess('1');
        $this->assertTrue($this->obj->success());
    }

    public function testSuccessUrl()
    {
        $this->assertEquals('', $this->obj->successUrl());
        $ret = $this->obj->setSuccessUrl('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->successUrl());

        $this->assertEquals('', $this->obj->setSuccessUrl(null)->successUrl());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setSuccessUrl([]);
    }

    public function testSetFailureUrl()
    {
        $this->assertEquals('', $this->obj->failureUrl());
        $ret = $this->obj->setFailureUrl('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->failureUrl());

        $this->assertEquals('', $this->obj->setFailureUrl(null)->successUrl());

        $this->expectException('\InvalidArgumentException');
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
        $request = $this->createMock(RequestInterface::class);
        $response = new Response();

        $this->obj->expects($this->any())
            ->method('run')
            ->will($this->returnValue($response));

        $obj = $this->obj;
        $res = $obj($request, $response);

        $this->assertInstanceOf(Response::class, $res);
    }

    public function testDefaultModeisJson()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = new Response();

        $this->obj->expects($this->any())
            ->method('run')
            ->will($this->returnValue($response));

        $obj = $this->obj;
        $res = $obj($request, $response);

        $headers = $res->getHeaders();
        $this->assertEquals('application/json', $headers['Content-Type'][0]);
    }

    public function testInvokeModeJson()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = new Response();

        $this->obj->expects($this->any())
            ->method('run')
            ->will($this->returnValue($response));

        $this->obj->setMode('json');
        $obj = $this->obj;
        $res = $obj($request, $response);

        $headers = $res->getHeaders();
        $this->assertEquals('application/json', $headers['Content-Type'][0]);
    }

    public function testInvokeModeXml()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = new Response();

        $this->obj->expects($this->any())
            ->method('run')
            ->will($this->returnValue($response));

        $this->obj->setMode('xml');
        $obj = $this->obj;
        $res = $obj($request, $response);

        $headers = $res->getHeaders();
        $this->assertEquals('text/xml', $headers['Content-Type'][0]);
    }

    public function testInvokeModeRedirect()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = new Response();

        $this->obj->expects($this->any())
            ->method('run')
            ->will($this->returnValue($response));

        $this->obj->setMode('redirect');
        $this->obj->setFailureUrl('example.com');
        $obj = $this->obj;
        $res = $obj($request, $response);

        $this->assertEquals(301, $res->getStatusCode());

        $headers = $res->getHeaders();
        $this->assertEquals('example.com', $headers['Location'][0]);
    }

    public function testInitIsTrue()
    {
        $request = $this->createMock(RequestInterface::class);
        $this->assertTrue($this->obj->init($request));
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerLogger($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
