<?php

namespace Charcoal\Tests\App\ServiceProvider;

// From PHPUnit
use \PHPUnit_Framework_TestCase;

// From PSR-3
use \Psr\Log\NullLogger;

// From `tedivm/stash`
use \Stash\Driver\BlackHole;
use \Stash\Pool;

// From Pimple
use \Pimple\Container;

// From 'charcoal-app'
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
                'base_path' => realpath(__DIR__.'/../../..')
            ]
        ]);
        $container['cache'] = function ($container) {
            $driver = new BlackHole();
            $pool   = new Pool($driver);

            return $pool;
        };
        $container['logger'] = function($c) {
            return new NullLogger();
        };
        $provider = new TranslatorServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['translator/config']));
        $this->assertTrue(isset($container['translator/locales']));
    }
}
