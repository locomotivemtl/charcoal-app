<?php

namespace Charcoal\Tests\App\ServiceProvider;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Charcoal\App\ServiceProvider\CacheServiceProvider;

/**
 *
 */
class CacheServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new CacheServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['cache/config']));
        $this->assertTrue(isset($container['cache/driver']));
        $this->assertTrue(isset($container['cache']));
    }
}
