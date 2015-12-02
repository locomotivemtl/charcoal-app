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
        $this->obj = new \Charcoal\App\Route\ActionRoute([
            'app'       => $this->app, 
            'logger'    => $this->app->logger(),
            'config'    => $route_config
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\Charcoal\App\Route\ActionRoute', $this->obj);
    }
}
