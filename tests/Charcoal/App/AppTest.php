<?php

namespace Charcoal\Tests\App;

use Psr\Http\Message\ResponseInterface;

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

/**
 *
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var App
     */
    public $obj;

    public function setUp()
    {
        $config = new AppConfig();
        $this->obj = new App(new AppContainer([
            'config'=>$config
        ]));
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(App::class, $this->obj);
    }

    public function testRun()
    {
        $res = $this->obj->run();
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
