<?php

namespace Charcoal\Tests\App\Action;

use \Charcoal\App\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $config = new \Charcoal\App\AppConfig();
        $app = new \Slim\App();

        $container = $app->getContainer();
        $container['logger'] = function($c) {
            return $GLOBALS['logger'];
        };

        $this->obj = new App([
            'config'=>$config,
            'app'=>$app
        ]);
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\App\App', $obj);
    }
}
