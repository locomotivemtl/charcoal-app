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
        $ret = $this->obj->set_mode('redirect');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('redirect', $this->obj->mode());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->set_mode(false);
    }
}
