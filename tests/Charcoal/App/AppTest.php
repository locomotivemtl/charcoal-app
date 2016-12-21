<?php

namespace Charcoal\Tests\App;

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
        $obj = $this->obj;
        $this->assertInstanceOf(App::class, $obj);
    }
}
