<?php

namespace Charcoal\Tests\App\Route;

// From 'charcoal-app'
use Charcoal\App\App;
use Charcoal\App\Route\RouteManager;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class RouteManagerTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var RouteManager
     */
    public $obj;

    /**
     * Charcoal Application.
     *
     * @var App
     */
    public $app;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->app = App::instance();
        $this->obj = new RouteManager([
            'config' => [],
            'app'    => $this->app
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(RouteManager::class, $this->obj);
    }

    public function testSetupTemplate()
    {
        $obj = new RouteManager([
            'config' => [
                'templates' => [
                    'foo', [
                        'ident'  => 'test',
                        'method' => ['GET', 'POST']
                    ]
                ]
            ],
            'app' => $this->app
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
                        'ident'  => 'test',
                        'method' => ['GET', 'POST']
                    ]
                ]
            ],
            'app' => $this->app
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
                        'ident'  => 'test',
                        'method' => ['GET', 'POST']
                    ]
                ]
            ],
            'app' => $this->app
        ]);
        //$ret = $obj->setupRoutes();
        //$this->assertInstanceOf('\Slim\Route', $ret);
    }
}
