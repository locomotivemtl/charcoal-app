<?php

namespace Charcoal\Tests\App\ServiceProvider;

use Pimple\Container;

// Dependencies from `league/flysystem`
use League\Flysystem\MountManager;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Adapter\NullAdapter;

// Dependency from `league/flysystem-aws-s3-v3`
use League\Flysystem\AwsS3v3\AwsS3Adapter;

// Dependency from `league/flysystem-dropbox`
use League\Flysystem\Dropbox\DropboxAdapter;

// Dependency from `league/flysystem-sftp`
use League\Flysystem\Sftp\SftpAdapter;

use Charcoal\App\Config\FilesystemConfig;
use Charcoal\App\ServiceProvider\FilesystemServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class FilesystemServiceProviderTest extends AbstractTestCase
{
    private $obj;

    public function setUp(): void
    {
        $this->obj = new FilesystemServiceProvider();
    }

    public function testProvider()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => []
            ]
        ]);

        $this->assertTrue(isset($container['filesystem/config']));
        $this->assertTrue(isset($container['filesystem/manager']));
        $this->assertTrue(isset($container['filesystems']));

        $this->assertInstanceOf(FilesystemConfig::class, $container['filesystem/config']);
        $this->assertInstanceOf(MountManager::class, $container['filesystem/manager']);
        $this->assertInstanceOf(Container::class, $container['filesystems']);
    }

    public function testProviderDefaultAdapters()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => []
            ]
        ]);

        $this->assertTrue(isset($container['filesystems']['private']));
        $this->assertTrue(isset($container['filesystems']['public']));

        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['private']);
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['public']);
    }

    public function testProviderLocalAdapter()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => [
                    'connections' => [
                        'local' => [
                            'type' => 'local',
                            'path' => '/',
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue(isset($container['filesystems']['local']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['local']);
    }

    public function testProviderS3Adapter()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => [
                    'connections' => [
                        's3' => [
                            'type'   => 's3',
                            'key'    => 'key',
                            'secret' => 'secret',
                            'bucket' => 'bucket',
                            'region' => 'region',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue(isset($container['filesystems']['s3']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['s3']);
    }

    public function testProviderFtpAdapter()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => [
                    'connections' => [
                        'ftp' => [
                            'type'      => 'ftp',
                            'host'      => 'localhost',
                            'username'  => 'username',
                            'password'  => 'password'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue(isset($container['filesystems']['ftp']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['ftp']);
    }

    public function testProviderSftpAdapter()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => [
                    'connections' => [
                        'sftp' => [
                            'type'      => 'sftp',
                            'host'      => 'localhost',
                            'username'  => 'username',
                            'password'  => 'password'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue(isset($container['filesystems']['sftp']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['sftp']);
    }

    public function testProviderMemorypAdapter()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => [
                    'connections' => [
                        'memory' => [
                            'type'  => 'memory'
                        ]
                    ]
                ]
            ]
        ]);


        $this->assertTrue(isset($container['filesystems']['memory']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['memory']);
    }

    public function testProviderNullAdapter()
    {
        $container = $this->getContainer([
            'config' => [
                'filesystem' => [
                    'connections' => [
                        'test' => [
                            'type' => 'noop'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue(isset($container['filesystems']['test']));
        $this->assertInstanceOf(Filesystem::class, $container['filesystems']['test']);
    }

    public function testConfigWithoutTypeThrowsException()
    {
        $this->expectException('\Exception');
        $container = $this->getContainer([
            'config' => [
                'filesystem' => [
                    'connections' => [
                        'test' => []
                    ]
                ]
            ]
        ]);
        $test = $container['filesystem/test'];
    }

    private function getContainer($defaults = null)
    {
        $container = new Container($defaults);
        $this->obj->register($container);

        return $container;
    }
}
