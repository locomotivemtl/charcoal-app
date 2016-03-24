<?php

namespace Charcoal\Tests\App\Template;

use \Charcoal\App\App;

class AbstractWidgetTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\App\Template\AbstractWidget', [[
                'app'=>$GLOBALS['app'],
                'logger'=>new \Psr\Log\NullLogger()
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
}
