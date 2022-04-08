<?php

namespace Charcoal\App\ServiceProvider;

use Exception;
use LogicException;
use InvalidArgumentException;
use UnexpectedValueException;

// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;

// From 'aws/aws-sdk-php'
use Aws\S3\S3Client;

// From 'league/flysystem'
use League\Flysystem\MountManager;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Adapter\NullAdapter;

// From 'league/flysystem-aws-s3-v3'
use League\Flysystem\AwsS3v3\AwsS3Adapter;


// From 'league/flysystem-sftp'
use League\Flysystem\Sftp\SftpAdapter;

// From 'league/flysystem-memory'
use League\Flysystem\Memory\MemoryAdapter;

// From 'charcoal-app'
use Charcoal\App\Config\FilesystemConfig;

/**
 *
 */
class FilesystemServiceProvider implements ServiceProviderInterface
{
    /**
     * @param  Container $container A service container.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param  Container $container A service container.
         * @return FilesystemConfig
         */
        $container['filesystem/config'] = function (Container $container) {
            $fsConfig = ($container['config']['filesystem'] ?? null);
            return new FilesystemConfig($fsConfig);
        };

        /**
         * @param  Container $container A service container.
         * @return MountManager
         */
        $container['filesystem/manager'] = function () {
            return new MountManager();
        };

        /**
         * @param  Container $container A service container.
         * @return array<string, Filesystem>
         */
        $container['filesystems'] = function (Container $container) {
            $filesystemConfig = $container['filesystem/config'];
            $filesystems = new Container();

            foreach ($filesystemConfig['connections'] as $ident => $connection) {
                $fs = $this->createConnection($connection);
                $filesystems[$ident] = $fs;
                $container['filesystem/manager']->mountFilesystem($ident, $fs);
            }

            return $filesystems;
        };
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws Exception If the filesystem type is not defined in config.
     * @throws UnexpectedValueException If the filesystem type is invalid / unsupported.
     * @return Filesystem
     */
    private function createConnection(array $config)
    {
        if (!isset($config['type'])) {
            throw new Exception(
                'No filesystem type defined'
            );
        }

        $type = $config['type'];

        switch ($type) {
            case 'local':
                $adapter = $this->createLocalAdapter($config);
                break;

            case 's3':
                $adapter = $this->createS3Adapter($config);
                break;

            case 'ftp':
                $adapter = $this->createFtpAdapter($config);
                break;

            case 'sftp':
                $adapter = $this->createSftpAdapter($config);
                break;

            case 'memory':
                $adapter = $this->createMemoryAdapter();
                break;

            case 'noop':
                $adapter = $this->createNullAdapter();
                break;

            default:
                throw new UnexpectedValueException(
                    sprintf('Invalid filesystem type "%s"', $type)
                );
        }

        return new Filesystem($adapter);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws InvalidArgumentException If the path is not defined.
     * @throws LogicException If the path is not accessible.
     * @return LocalAdapter
     */
    private function createLocalAdapter(array $config)
    {
        if (!isset($config['path']) || !$config['path']) {
            throw new InvalidArgumentException(
                'No "path" configured for local filesystem.'
            );
        }

        $path = realpath($config['path']);
        if ($path === false) {
            throw new LogicException(
                'Filesystem "path" does not exist.'
            );
        }

        $defaults = [
            'lock'        => null,
            'links'       => null,
            'permissions' => [],
        ];
        $config = array_merge($defaults, $config);

        return new LocalAdapter($config['path'], $config['lock'], $config['links'], $config['permissions']);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws InvalidArgumentException If the key, secret or bucket is not defined in config.
     * @return AwsS3Adapter
     */
    private function createS3Adapter(array $config)
    {
        if (!isset($config['key']) || !$config['key']) {
            throw new InvalidArgumentException(
                'No "key" configured for S3 filesystem.'
            );
        }

        if (!isset($config['secret']) || !$config['secret']) {
            throw new InvalidArgumentException(
                'No "secret" configured for S3 filesystem.'
            );
        }

        if (!isset($config['bucket']) || !$config['bucket']) {
            throw new InvalidArgumentException(
                'No "bucket" configured for S3 filesystem.'
            );
        }

        $defaults = [
            'region'  => '',
            'version' => 'latest',
            'prefix'  => null,
        ];
        $config = array_merge($defaults, $config);

        $client = S3Client::factory([
            'credentials' => [
                'key'     => $config['key'],
                'secret'  => $config['secret'],
            ],
            'region'      => $config['region'],
            'version'     => $config['version'],
        ]);

        if (isset($config['public']) && !$config['public']) {
            $permissions = null;
        } else {
            $permissions = [
                'ACL' => 'public-read',
            ];
        }

        return new AwsS3Adapter($client, $config['bucket'], $config['prefix'], $permissions);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws InvalidArgumentException If the host, username or password is not defined in config.
     * @return FtpAdapter
     */
    private function createFtpAdapter(array $config)
    {
        if (!$config['host']) {
            throw new InvalidArgumentException(
                'No host configured for FTP filesystem adapter.'
            );
        }

        if (!$config['username']) {
            throw new InvalidArgumentException(
                'No username configured for FTP filesystem adapter.'
            );
        }

        if (!$config['password']) {
            throw new InvalidArgumentException(
                'No password configured for FTP filesystem adapter.'
            );
        }

        $defaults = [
            'port'    => null,
            'root'    => null,
            'passive' => null,
            'ssl'     => null,
            'timeout' => null,
        ];
        $config = array_merge($defaults, $config);

        return new FtpAdapter($config);
    }

    /**
     * @param  array $config The driver (adapter) configuration.
     * @throws InvalidArgumentException If the host, username or password is not defined in config.
     * @return SftpAdapter
     */
    private function createSftpAdapter(array $config)
    {
        if (!$config['host']) {
            throw new InvalidArgumentException(
                'No host configured for SFTP filesystem adapter.'
            );
        }

        if (!$config['username']) {
            throw new InvalidArgumentException(
                'No username configured for SFTP filesystem adapter.'
            );
        }

        if (!$config['password']) {
            throw new InvalidArgumentException(
                'No password configured for SFTP filesystem adapter.'
            );
        }

        $defaults = [
            'port'       => null,
            'privateKey' => null,
            'root'       => null,
            'timeout'    => null,
        ];
        $config = array_merge($defaults, $config);

        return new SftpAdapter($config);
    }

    /**
     * @return MemoryAdapter
     */
    private function createMemoryAdapter()
    {
        return new MemoryAdapter();
    }

    /**
     * @return NullAdapter
     */
    private function createNullAdapter()
    {
        return new NullAdapter();
    }
}
