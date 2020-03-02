<?php

namespace Charcoal\Tests\App;

use PDO;

// From Mockery
use Mockery;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash' (PSR-6)
use Stash\Pool;

// From Slim
use Slim\Http\Uri;

// From Pimple
use Pimple\Container;

// From 'league/climate'
use League\CLImate\CLImate;
use League\CLImate\Util\System\Linux;
use League\CLImate\Util\Output;
use League\CLImate\Util\Reader\Stdin;
use League\CLImate\Util\UtilFactory;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-cache'
use Charcoal\Cache\CacheConfig;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\Module\ModuleInterface;
use Charcoal\App\Template\WidgetBuilder;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

// From 'charcoal-view'
use Charcoal\View\GenericView;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\MustacheLoader;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;

/**
 *
 */
class ContainerProvider
{
    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseServices(Container $container)
    {
        $this->registerConfig($container);
        $this->registerBaseUrl($container);
        $this->registerDatabase($container);
        $this->registerLogger($container);
        $this->registerCache($container);
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseUrl(Container $container)
    {
        $container['base-url'] = function (Container $container) {
            return Uri::createFromString('https://example.com:8080/foo/bar?abc=123');
        };
    }

    /**
     * Setup the application configset.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerConfig(Container $container)
    {
        $container['config'] = function (Container $container) {
            return new AppConfig([
                'base_path' => realpath(__DIR__.'/../../..'),
            ]);
        };
    }

    public function registerWidgetFactory(Container $container)
    {
        $this->registerLogger($container);

        $container['widget/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options' => [
                    'suffix' => 'Widget'
                ],
                'arguments' => [[
                    'container' => $container,
                    'logger'    => $container['logger']
                ]]
            ]);
        };
    }

    public function registerWidgetBuilder(Container $container)
    {
        $this->registerWidgetFactory($container);

        $container['widget/builder'] = function (Container $container) {
            return new WidgetBuilder($container['widget/factory'], $container);
        };
    }

    public function registerClimate(Container $container)
    {
        $container['climate/system'] = function (Container $container) {
            $system = Mockery::mock(Linux::class);
            $system->shouldReceive('hasAnsiSupport')->andReturn(true);
            $system->shouldReceive('width')->andReturn(80);

            return $system;
        };

        $container['climate/output'] = function (Container $container) {
            $output = Mockery::mock(Output::class);
            $output->shouldReceive('persist')->andReturn($output);
            $output->shouldReceive('sameLine')->andReturn($output);
            $output->shouldReceive('write');

            return $output;
        };

        $container['climate/reader'] = function (Container $container) {
            $reader = Mockery::mock(Stdin::class);
            $reader->shouldReceive('line')->andReturn('line');
            $reader->shouldReceive('char')->andReturn('char');
            $reader->shouldReceive('multiLine')->andReturn('multiLine');
            return $reader;
        };

        $container['climate/util'] = function (Container $container) {
            return new UtilFactory($container['climate/system']);
        };

        $container['climate'] = function (Container $container) {
            $climate = new CLImate();

            $climate->setOutput($container['climate/output']);
            $climate->setUtil($container['climate/util']);
            $climate->setReader($container['climate/reader']);

            return $climate;
        };
    }

    /**
     * Setup the framework's view renderer.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerView(Container $container)
    {
        $container['view/loader'] = function (Container $container) {
            return new MustacheLoader([
                'logger'    => $container['logger'],
                'base_path' => $container['config']['base_path'],
                'paths'     => [
                    'views'
                ]
            ]);
        };

        $container['view/engine'] = function (Container $container) {
            return new MustacheEngine([
                'logger' => $container['logger'],
                'cache'  => MustacheEngine::DEFAULT_CACHE_PATH,
                'loader' => $container['view/loader']
            ]);
        };

        $container['view'] = function (Container $container) {
            return new GenericView([
                'logger' => $container['logger'],
                'engine' => $container['view/engine']
            ]);
        };
    }

    /**
     * Setup the application's translator service.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerTranslator(Container $container)
    {
        $container['locales/manager'] = function (Container $container) {
            return new LocalesManager([
                'locales' => [
                    'en' => [ 'locale' => 'en-US' ]
                ]
            ]);
        };

        $container['translator'] = function (Container $container) {
            return new Translator([
                'manager' => $container['locales/manager']
            ]);
        };
    }

    /**
     * Setup the application's logging interface.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerLogger(Container $container)
    {
        $container['logger'] = function (Container $container) {
            return new NullLogger();
        };
    }

    /**
     * Setup the application's caching interface.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerCache(Container $container)
    {
        $container['cache/config'] = function (Container $container) {
            return new CacheConfig();
        };

        $container['cache'] = function ($container) {
            return new Pool();
        };
    }

    public function registerDatabase(Container $container)
    {
        $container['database'] = function (Container $container) {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    public function registerMetadataLoader(Container $container)
    {
        $this->registerLogger($container);
        $this->registerCache($container);

        $container['metadata/loader'] = function (Container $container) {
            return new MetadataLoader([
                'logger'    => $container['logger'],
                'cache'     => $container['cache'],
                'base_path' => $container['config']['base_path'],
                'paths'     => [
                    'metadata',
                    'vendor/locomotivemtl/charcoal-object/metadata',
                    'vendor/locomotivemtl/charcoal-user/metadata'
                ]
            ]);
        };
    }

    public function registerSourceFactory(Container $container)
    {
        $this->registerLogger($container);
        $this->registerDatabase($container);

        $container['source/factory'] = function (Container $container) {
            return new Factory([
                'map' => [
                    'database' => DatabaseSource::class
                ],
                'arguments'  => [[
                    'logger' => $container['logger'],
                    'pdo'    => $container['database']
                ]]
            ]);
        };
    }

    public function registerPropertyFactory(Container $container)
    {
        $this->registerTranslator($container);
        $this->registerDatabase($container);
        $this->registerLogger($container);

        $container['property/factory'] = function (Container $container) {
            return new Factory([
                'resolver_options' => [
                    'prefix' => '\\Charcoal\\Property\\',
                    'suffix' => 'Property'
                ],
                'arguments' => [[
                    'container'  => $container,
                    'database'   => $container['database'],
                    'translator' => $container['translator'],
                    'logger'     => $container['logger']
                ]]
            ]);
        };
    }

    public function registerModelFactory(Container $container)
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerMetadataLoader($container);
        $this->registerPropertyFactory($container);
        $this->registerSourceFactory($container);

        $container['model/factory'] = function (Container $container) {
            return new Factory([
                'arguments' => [[
                    'container'        => $container,
                    'logger'           => $container['logger'],
                    'metadata_loader'  => $container['metadata/loader'],
                    'property_factory' => $container['property/factory'],
                    'source_factory'   => $container['source/factory']
                ]]
            ]);
        };
    }

    public function registerCollectionLoader(Container $container)
    {
        $this->registerLogger($container);
        $this->registerModelFactory($container);

        $container['model/collection/loader'] = function (Container $container) {
            return new \Charcoal\Loader\CollectionLoader([
                'logger'  => $container['logger'],
                'factory' => $container['model/factory']
            ]);
        };
    }

    public function registerModuleFactory(Container $container)
    {
        $this->registerLogger($container);
        $this->registerDatabase($container);

        $container['module/factory'] = function (Container $container) {
            return new Factory([
                'base_class'       => ModuleInterface::class,
                'resolver_options' => [
                    'suffix' => 'Module'
                ],
                'arguments'  => [[
                    'logger' => $container['logger']
                ]]
            ]);
        };
    }

    public function registerAppDependencies(Container $container)
    {
        $this->registerConfig($container);
        $this->registerBaseUrl($container);
        $this->registerLogger($container);
        $this->registerCache($container);
        $this->registerTranslator($container);
        $this->registerModuleFactory($container);
    }

    public function registerActionDependencies(Container $container)
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerBaseUrl($container);
    }

    public function registerTemplateDependencies(Container $container)
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerBaseUrl($container);
    }

    public function registerWidgetDependencies(Container $container)
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerView($container);
        $this->registerBaseUrl($container);
    }
}
