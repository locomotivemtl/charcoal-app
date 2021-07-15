<?php

namespace Charcoal\Tests\App\Script;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-app'
use Charcoal\App\Script\AbstractScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\App\ContainerProvider;

/**
 *
 */
class AbstractScriptTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var AbstractScript
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

        $this->obj = $this->getMockForAbstractClass(AbstractScript::class, [[
            'climate'   => $container['climate'],
            'logger'    => $container['logger'],
            'container' => $container
        ]]);
    }

    public function testInvoke()
    {
        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $invoke   = call_user_func([$this->obj, '__invoke'], $request, $response);
    }

    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());
    }

    public function testSetDescription()
    {
        $ret = $this->obj->setDescription('Foo Description');
        $this->assertSame($ret, $this->obj);
        $this->assertEQuals('Foo Description', $this->obj->description());
    }

    public function testSetQuiet()
    {
        $this->assertFalse($this->obj->quiet());
        $ret = $this->obj->setQuiet(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->quiet());
    }

    public function testSetVerbose()
    {
        $this->assertFalse($this->obj->verbose());
        $ret = $this->obj->setVerbose(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->verbose());
    }

    public function testSetArguments()
    {
        $defaultArgs = $this->obj->arguments();
        $ret = $this->obj->setArguments([
            'foo'=>[]
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertArrayHasKey('foo', $this->obj->arguments());
        $this->assertEquals([], $this->obj->argument('foo'));
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
            $containerProvider->registerClimate($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
