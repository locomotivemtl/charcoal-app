<?php

namespace Charcoal\Tests\App\Route;


use Slim\Interfaces\RouteInterface;

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
    public function setUp(): void
    {
        $this->app = App::instance();
        $this->obj = new RouteManager([
            'config' => [],
            'app'    => $this->app
        ]);
        $this->obj->setupRoutes();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(RouteManager::class, $this->obj);
    }

    public function testSetupTemplate()
    {
        $config = [
            'templates' => [
                'foo' => [
                    'ident'  => 'test',
                    'method' => ['GET', 'POST']
                ]
            ]
        ];
        $obj = new RouteManager([
            'config' =>$config,
            'app' => $this->app
        ]);

        $reflector = new \ReflectionObject($obj);
        $method = $reflector->getMethod('setupTemplate');
        $method->setAccessible(true);
        foreach($config['templates'] as $routeIdent => $templateConfig) {
            $ret = $method->invoke($obj, $routeIdent, $templateConfig);
            $this->assertInstanceOf(RouteInterface::class, $ret);
        }
    }

    public function testSetupAction()
    {
        $config = [
            'actions' => [
                'foo' => [
                    'ident'  => 'test',
                    'method' => ['GET', 'POST']
                ]
            ]
        ];
        $obj = new RouteManager([
            'config' => $config,
            'app' => $this->app
        ]);

        $reflector = new \ReflectionObject($obj);
        $method = $reflector->getMethod('setupAction');
        $method->setAccessible(true);
        foreach($config['actions'] as $routeIdent => $actionConfig) {
            $ret = $method->invoke($obj, $routeIdent, $actionConfig);
            $this->assertInstanceOf(RouteInterface::class, $ret);
        }
    }

    public function testSetupScript()
    {
        $config = [
            'scripts' => [
                'foo' => [
                    'ident'  => 'test',
                    'method' => ['GET', 'POST']
                ]
            ]
        ];
        $obj = new RouteManager([
            'config' => $config,
            'app' => $this->app
        ]);

        $reflector = new \ReflectionObject($obj);
        $method = $reflector->getMethod('setupScript');
        $method->setAccessible(true);
        foreach($config['scripts'] as $routeIdent => $scriptConfig) {
            $ret = $method->invoke($obj, $routeIdent, $scriptConfig);
            $this->assertInstanceOf(RouteInterface::class, $ret);
        }
    }
}
