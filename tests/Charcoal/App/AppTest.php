<?php

namespace Charcoal\Tests\App;

// From PSR-7
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-app'
use Charcoal\App\App;
use Charcoal\App\AppConfig;
use Charcoal\App\AppContainer;

/**
 *
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var App
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
    public function setUp()
    {
        $config    = new AppConfig();
        $container = new AppContainer([
            'config' => $config
        ]);

        $this->obj = new App($container);
    }

    public function testAppIsConstructed()
    {
        $app = new App();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(App::class, $this->obj);
    }

    public function testRun()
    {
        $res = $this->obj->run(true);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
