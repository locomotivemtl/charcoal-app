<?php

namespace Charcoal\Tests\App\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-app'
use Charcoal\App\Route\ScriptRoute;
use Charcoal\App\Route\ScriptRouteConfig;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\App\ContainerProvider;

/**
 *
 */
class ScriptRouteTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var ScriptRoute
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $this->obj = new ScriptRoute([
            'config' => new ScriptRouteConfig([
                'controller' => 'foo/bar'
            ])
        ]);
    }

    public function testInvoke()
    {
        $container = $this->container();

        $container['script/factory'] = function($c) {
            return new Factory();
        };

        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        // Invalid because "foo/bar" is not a valid script controller
        $this->expectException('\Exception');
        $ret = call_user_func([$this->obj, '__invoke'], $container, $request, $response);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerLogger($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
