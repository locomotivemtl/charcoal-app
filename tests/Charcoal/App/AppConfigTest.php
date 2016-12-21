<?php

namespace Charcoal\Tests\App;

use \Charcoal\App\AppConfig;

class AppConfigTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function testConstructor()
    {
        $obj = new AppConfig();
        $this->assertInstanceOf(AppConfig::class, $obj);
    }
}
