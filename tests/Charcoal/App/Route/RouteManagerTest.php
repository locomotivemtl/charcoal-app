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
            'app'    => $GLOBALS['app'],
            'logger' => new \Psr\Log\NullLogger()
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('\Charcoal\App\Route\RouteManager', $this->obj);
    }

    public function testSetupTemplate()
    {
        $ret = $this->obj->setupTemplate('foo', [
            'ident' => 'test',
            'method' => ['GET', 'POST']
        ]);
        $this->assertInstanceOf('\Slim\Route', $ret);
    }

    public function testSetupAction()
    {
        $ret = $this->obj->setupAction('foo', [
            'ident' => 'test',
            'method' => ['GET', 'POST']
        ]);
        $this->assertInstanceOf('\Slim\Route', $ret);
    }

    public function testSetupScript()
    {
        $ret = $this->obj->setupScript('foo', [
            'ident' => 'test',
            'method' => ['GET', 'POST']
        ]);
        $this->assertInstanceOf('\Slim\Route', $ret);
    }
}
