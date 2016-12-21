<?php

namespace Charcoal\Tests\App\ServiceProvider;

use \PHPUnit_Framework_TestCase;

use \Pimple\Container;

use \Charcoal\App\ServiceProvider\TranslatorServiceProvider;

/**
 *
 */
class TranslatorServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => [
                'base_path' => ''
            ],
            'cache' => null
        ]);
        $provider = new TranslatorServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['translator/config']));
        $this->assertTrue(isset($container['translator/locales']));
    }
}
