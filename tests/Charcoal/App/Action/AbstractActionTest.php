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
        $this->obj = $this->getMockForAbstractClass('\Charcoal\App\Action\AbstractAction', [[
            'app'=>$this->app,
            'logger'=>$this->app->logger()
        ]]);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\App\Action\AbstractAction', $obj);
    }

    public function testSetLang()
    {
        $this->assertNull($this->obj->language());
        $ret = $this->obj->set_language('fr');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('fr', $this->obj->language());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->set_language(false);
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
