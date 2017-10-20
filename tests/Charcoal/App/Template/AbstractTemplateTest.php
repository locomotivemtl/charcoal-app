<?php

namespace Charcoal\Tests\App\Template;

// From PSR-7
use \Psr\Http\Message\RequestInterface;

// From Slim
use \Slim\Http\Response;

// From Pimple
use \Pimple\Container;

// From 'charcoal-app'
use \Charcoal\App\Template\AbstractTemplate;
use \Charcoal\Tests\App\ContainerProvider;

/**
 *
 */
class AbstractTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var AbstractTemplate
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
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $this->getMockForAbstractClass(AbstractTemplate::class, [[
            'logger'    => $container['logger'],
            'container' => $container
        ]]);
    }

    public function testInitIsTrue()
    {
        $request = $this->createMock(RequestInterface::class);
        $this->assertTrue($this->obj->init($request));
    }

    public function testSetDependencies()
    {
        $container = new Container();
        $res = $this->obj->setDependencies($container);
        $this->assertNull($res);
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
