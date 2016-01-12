<?php

namespace Charcoal\Tests\App\Route;

use \Charcoal\App\App;

class ActionRouteTest extends \PHPUnit_Framework_TestCase
{
    public $app;
    public $obj;

    public function setUp()
    {
        $route_config = [];
        $this->app = $GLOBALS['app'];
        $container = $this->app->getContainer();
        $this->obj = new \Charcoal\App\Route\ActionRoute([
            'app'       => $this->app,
            'logger'    => $container['logger'],
            'config'    => $route_config
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\Charcoal\App\Route\ActionRoute', $this->obj);
    }
}
