<?php

namespace Charcoal\Tests\App\Template;

use \PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use \Psr\Http\Message\RequestInterface;

use \Pimple\Container;

use \Charcoal\App\Template\AbstractTemplate;

/**
 *
 */
class AbstractTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object under test
     * @var AbstractTemplate
     */
    public $obj;

    public function setUp()
    {
        $container = new Container();
        $this->obj = $this->getMockForAbstractClass(AbstractTemplate::class, [[
            'logger'    => new NullLogger(),
            'container' => $container
        ]]);
    }

    public function testInitIsTrue()
    {
        $request = $this->getMock(RequestInterface::class);
        $this->assertTrue($this->obj->init($request));
    }

    public function testSetDependencies()
    {
        $container = new Container();
        $res = $this->obj->setDependencies($container);
        $this->assertNull($res);
    }
}
