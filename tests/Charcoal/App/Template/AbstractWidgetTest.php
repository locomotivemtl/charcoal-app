<?php

namespace Charcoal\Tests\App\Template;

use \PHPUnit_Framework_TestCase;

use \Psr\Log\NullLogger;

use \Pimple\Container;

use \Charcoal\App\Template\AbstractWidget;

class AbstractWidgetTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container();
        $this->obj = $this->getMockForAbstractClass(AbstractWidget::class, [[
            'logger' => new NullLogger(),
            'container' => $container
        ]]);
    }

    /**
     * Assert that the widget:
     * - active default state is true
     * - `setActive()` method is chainable
     * - `setActive()` actually sets the active value.
     */
    public function testSetActive()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->active());
        $ret = $obj->setActive(false);
        $this->assertSame($ret, $obj);
        $this->assertFalse($obj->active());
    }

    public function testSetDependencies()
    {
        $container = new Container();
        $res = $this->obj->setDependencies($container);
        $this->assertNull($res);
    }
}
