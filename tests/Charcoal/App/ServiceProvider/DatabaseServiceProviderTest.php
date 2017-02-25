<?php

namespace Charcoal\Tests\App\ServiceProvider;

use \Pimple\Container;

use \Charcoal\App\ServiceProvider\DatabaseServiceProvider;

/**
 *
 */
class DatabaseServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new DatabaseServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['databases/config']));
        $this->assertTrue(isset($container['databases']));
        $this->assertTrue(isset($container['database/config']));
        $this->assertTrue(isset($container['database']));
    }
}
