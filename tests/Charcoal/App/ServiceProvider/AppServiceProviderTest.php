<?php

namespace Charcoal\Tests\App\ServiceProvider;

use Pimple\Container;

use Charcoal\App\ServiceProvider\AppServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AppServiceProviderTest extends AbstractTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $provider  = new AppServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['base-url']));
        $this->assertTrue(isset($container['route/factory']));
        $this->assertTrue(isset($container['action/factory']));
        $this->assertTrue(isset($container['template/factory']));
        $this->assertTrue(isset($container['widget/factory']));
        $this->assertTrue(isset($container['widget/builder']));
        $this->assertTrue(isset($container['module/factory']));
    }
}
