<?php

namespace Charcoal\Tests\App\ServiceProvider;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Charcoal\App\ServiceProvider\AppServiceProvider;

/**
 *
 */
class AppServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new AppServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['base-url']));
        $this->assertTrue(isset($container['route/factory']));
        $this->assertTrue(isset($container['action/factory']));
        $this->assertTrue(isset($container['script/factory']));
        $this->assertTrue(isset($container['template/factory']));
        $this->assertTrue(isset($container['widget/factory']));
        $this->assertTrue(isset($container['widget/builder']));
        $this->assertTrue(isset($container['module/factory']));
        $this->assertTrue(isset($container['climate/reader']));
        $this->assertTrue(isset($container['climate']));
    }
}
