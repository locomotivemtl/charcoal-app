<?php

namespace Charcoal\Tests\App\ServiceProvider;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Charcoal\App\ServiceProvider\LoggerServiceProvider;

/**
 *
 */
class LoggerServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new LoggerServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['logger']));
    }
}
