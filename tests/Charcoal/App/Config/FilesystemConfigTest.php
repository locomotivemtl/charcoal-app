<?php

namespace Charcoal\Tests\App\Config;

use Charcoal\App\Config\FilesystemConfig;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class FilesystemConfigTest extends AbstractTestCase
{
    public $obj;

    public function setUp(): void
    {
        $this->obj = new FilesystemConfig();
    }

    public function testDefaultConnections()
    {
        $this->assertArrayHasKey('private', $this->obj->defaultConnections());
        $this->assertArrayHasKey('public', $this->obj->defaultConnections());

        $this->assertArrayHasKey('private', $this->obj->connections());
        $this->assertArrayHasKey('public', $this->obj->connections());
    }

    public function testConnectionsAlwaysHaveDefaultConnections()
    {
        $this->obj->setData([
            'connections' => [
                'foo' => [
                    'type' => 'ftp'
                ]
            ]
        ]);

        $this->assertArrayHasKey('foo', $this->obj->connections());
        $this->assertArrayHasKey('private', $this->obj->connections());
        $this->assertArrayHasKey('public', $this->obj->connections());
    }
}
