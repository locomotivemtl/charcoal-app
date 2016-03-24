<?php

namespace Charcoal\Tests\App\Route;

use \Pimple\Container;

use \Charcoal\App\Route\ScriptRoute;
use \Charcoal\App\Route\ScriptRouteConfig;

class ScriptRouteTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new ScriptRoute([
            'config' => new ScriptRouteConfig([
                'controller' => 'foo/bar'
            ])
        ]);
    }

    public function testInvoke()
    {
        $container = new Container();
        $container['script/factory'] = function($c) {
            $factory = new \Charcoal\App\Script\ScriptFactory();
            return $factory;
        };
        $container['logger'] = function($c) {
            return new \Psr\Log\NullLogger();
        };
        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');

        // Invalid because "foo/bar" is not a valid script controller
        $this->setExpectedException('\Exception');
        $ret = call_user_func([$this->obj, '__invoke'], $container, $request, $response);
    }
}
