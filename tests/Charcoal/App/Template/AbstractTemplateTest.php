<?php

namespace Charcoal\Tests\App\Template;

use \Charcoal\App\App;

class AbstractTemplateTest extends \PHPUnit_Framework_TestCase
{
    public $app;
    public $obj;

    public function setUp()
    {
        $this->app = $GLOBALS['app'];
        $container = $this->app->getContainer();
        $this->obj = $this->getMockForAbstractClass('\Charcoal\App\Template\AbstractTemplate', [[
                'app'=>$this->app,
                'logger'=>$container['logger']
            ]]);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\App\Template\AbstractTemplate', $obj);
    }
}
