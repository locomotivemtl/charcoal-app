<?php

namespace Charcoal\Tests\App\Route;

use \Charcoal\App\Route\RouteManager;

class RouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new RouteManager([
            'config' => [],
            'app'    => $GLOBALS['app']
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\Charcoal\App\Route\RouteManager', $this->obj);
    }

    public function testSetupTemplate()
    {
        $obj = new RouteManager([
            'config' => [
                'templates' => [
                    'foo', [
                        'ident' => 'test',
                        'method' => ['GET', 'POST']
                    ]
                ]
            ],
            'app'    => $GLOBALS['app']
        ]);
        $ret = $obj->setupRoutes();
        //$this->assertInstanceOf('\Slim\Route', $ret);
    }

    public function testSetupAction()
    {
        $obj = new RouteManager([
            'config' => [
                'actions' => [
                    'foo', [
                        'ident' => 'test',
                        'method' => ['GET', 'POST']
                    ]
                ]
            ],
            'app'    => $GLOBALS['app']
        ]);
        $ret = $obj->setupRoutes();
        //$this->assertInstanceOf('\Slim\Route', $ret);
    }

    public function testSetupScript()
    {
        $obj = new RouteManager([
            'config' => [
                'scripts' => [
                    'foo', [
                        'ident' => 'test',
                        'method' => ['GET', 'POST']
                    ]
                ]
            ],
            'app'    => $GLOBALS['app']
        ]);
        //$ret = $obj->setupRoutes();
        //$this->assertInstanceOf('\Slim\Route', $ret);
    }
}
