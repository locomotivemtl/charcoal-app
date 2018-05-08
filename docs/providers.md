# Service Providers

Dependencies and extensions are handled by a dependency container, using [Pimple][pimple], which can be defined via _service providers_ (`Pimple\ServiceProviderInterface`).

#### Included Providers

The Charcoal App comes with several providers out of the box. All of these are within the `Charcoal\App\ServiceProvider` namespace:

-   [`AppServiceProvider`](#app-service-provider)
-   [`DatabaseServicePovider`](#database-service-provider)
-   [`FilesystemServiceProvider`](#filesystem-service-provider)
-   [`LoggerServiceProvider`](#logger-service-provider)

#### External Providers

The Charcoal App requires a few providers from independent components. The following use their own namespace and are automatically injected via the `AppServiceProvider`:

-   [`CacheServiceProvider`](#cache-service-provider)
-   [`TranslatorServiceProvider`](#translator-service-provider)
-   [`ViewServiceProvider`](#view-service-provider)

Most providers expect the container to provide the `config` entry, which should hold the application's main configuration.



## Basic Services

Dependencies are handled with a `Pimple` dependency Container.

Basic "App" services are:

-   `cache`
    -   A cache storage service for the [Stash Cache Library][stash].
    -   Configured by `config['cache']`
    -   Provided by [`charcoal-cache`][charcoal-cache]
-   `config`
    -   A `\Charcoal\App\AppConfig` instance.
-   `database`
    -   The default _PDO_ database.
    -   From a pool of database, available through `databases`.
    -   Configured by `config['databases']` and `config['default_database']`.
-   `filesystems`
    - A (pimple) container of `\League\Flysystem\Filesystem`
    - Configured by `config['filesystem]`
    - Also provide a `\League\Flysystem\MountManager` as `filesystem/manager`. 
-   `logger`
    -   A `\Psr\Log\Logger` instance.
    -   Provided by _Monolog_.
    -   Configured by `config['logger']`
-   `translator`
    -   A `Charcoal\Translator\Translation` object for multilingual strings.
    -   A `Charcoal\Translator\Translator` service based on [Symfony's Translator][symfony/translation].
    -   A `Charcoal\Translator\LocalesManager` for managing available languages.
    -   Configured by `config['translator']` and `config['locales']`
    -   Provided by [`charcoal-translator`][charcoal-translator]
-   `view`
    -   A `Charcoal\View\ViewInterface` instance
    -   Typically a `\Charcoal\View\GenericView` object.
    -   Configured by `config['view']`
    -   Provided by [`charcoal-view`][charcoal-view]



## App Service Provider

The `AppServiceProvider`, or `charcoal/app/service-provider/app` provides the following services:

| Service              | Type                          | Description |
| -------------------- | ----------------------------- | ----------- |
| **notFoundHandler**  | `callback`                    | For 404 (Not Found) URLs. Slim requirement.
| **errorHandler**     | `callback`                    | For 500 (Error) URLs. Slim requirement.
| **action/factory**   | `ActionFactory`<sup>1</sup>   | To create actions.
| **script/factory**   | `ScriptFactory`<sup>2</sup>   | To create templates.
| **template/factory** | `TemplateFactory`<sup>3</sup> | To create templates.
| **widget/factory**   | `WidgetFactory`<sup>4</sup>   | To create widgets.

1. `\Charcoal\App\Action\ActionFactory`
2. `\Charcoal\App\Script\ScriptFactory`
3. `\Charcoal\App\Template\TemplateFatory`
4. `\Charcoal\App\Widget\WidgetFactory`

<small>All factories are implementations of `\Charcoal\Factory\FactoryInterface`</small>



## Cache Service Provider

> **External Provider**
> 
> See the [`locomotivemtl/charcoal-cache`][charcoal-cache] for more information on using the cache service.

The `CacheServiceProvider`, or `charcoal/app/service-provider/cache` provides the following servicers:

| Service       | Type                | Description |
| ------------- | ------------------- | ----------- |
| **cache**     | `\Stash\Pool`       | The default PSR-6 cache pool.


Also available are the following helpers:

| Helper Service              | Type                          | Description |
|:--------------------------- | ----------------------------- | ----------- |
| **cache/config**            | `CacheConfig`<sup>1</sup>     | Cache configuration.
| **cache/builder**           | `CacheBuilder`<sup>2</sup>    | Cache pool builder.
| **cache/available-drivers** | `array`<sup>3</sup>           | Available drivers on the system.
| **cache/drivers**           | `\Pimple\Contianer`           | Map of all the available Stash driver instances.
| **cache/driver**            | `DriverInterface`<sup>4</sup> | The Stash driver used by the default pool, `cache`.

1. `\Charcoal\Cache\CacheConfig`
2. `\Charcoal\Cache\CacheBuilder`
3. `\Stash\DriverList`
4. `\Stash\Interfaces\DriverInterface`

### Cache Config

| Key               | Type     | Default         | Description |
|:----------------- |:--------:|:---------------:| ----------- |
| **types**         | `array`  | `[ 'memory' ]`  | The cache types to attempt to use, in order.
| **default_ttl**   | `int`    | `0`             | Default _time-to-live_, in seconds.
| **prefix**        | `string` | `charcoal`      | The cache prefix, or namespace.

A full example, in JSON format:

```json
{
    "cache": {
        "types": [ "memcache", "memory" ],
        "default_ttl": 0,
        "prefix": "charcoal"
    }
}
```



## Database Service Provider

The `DatabaseServiceProvider`, or `charcoal/app/service-provider/database` provides the following services:

| Service       | Type                | Description |
| ------------- | ------------------- | ----------- |
| **database**  | `\PDO`              | The default database PDO object.
| **databases** | `\Pimple\Container` | A map (container) of all the available PDO instances.

Also available are the following helpers:

| Helper Service       | Type                         | Description |
| -------------------- | ---------------------------- | ----------- |
| **database/config**  | `DatabaseConfig`<sup>1</sup> | Default database config container.
| **databases/config** | `\Pimple\Container`          | A map (container) of all the available PDO instances.

1. `\Charcoal\App\Config\DatabaseConfig`

### Database Config

The databases are configured with the following options:

| Key                  | Type     | Default       | Description |
| -------------------- | -------- | ------------- | ----------- |
| **databases**        | `array`  |               |             |
| **default_database** | `string` |               |             |

The **database** config is as follow:

| Key               | Type     | Default       | Description |
| ----------------- | -------- | ------------- | ----------- |
| **type**          | `string` | `mysql`       | The database driver type.
| **hostname**      | `string` | `localhost`   | The database hostname or IP address.
| **username**      | `string` | `''`          | The username with access to this database / tables.
| **password**      | `string` | `''`          | The password, for username.
| **database**      | `string` | `''`          | The database name for this project. 
| **disable_utf8**  | `bool`   | `false`       | Set to true to disable automatic utf-8.

Or, in JSON format:

```json
{
    "databases": {
        "foobar": {
            "type": "mysql",
            "hostname": "dbserver.example.com",
            "username": "dbuser",
            "password": "dbpassword",
            "disable_utf8": false
        }
    },
    "default_database": "foobar"
}
```



## Filesystem Service Provider

The `FilesystemServiceProvider`, or `charcoal/app/service-provider/filesystem` provides the following services:

| Service                | Type                             | Description |
| ---------------------- | -------------------------------- | ----------- |
| **filesystems**        | `\Pimple\Container`              | A list of `\League\Flysystem\Filesystem`
| **filesystem/manager** | `\League\Flysystem\MountManager` | A mount manager.

Also available are the following helpers:

| Helper Service       | Type                             | Description |
| -------------------- | -------------------------------- | ----------- |
| **filesystem/config**  | `FilesystemConfig`<sup>1</sup> | Default filesystem config container.

1. `\Charcoal\App\Config\FilesystemConfig`

### Filesystem Config

| Key                    | Type     | Default    | Description |
| ---------------------- | -------- | ---------- | ----------- |
| **connections**        | `array`  | `...`      |             |
| **default_connection** | `string` | `'public'` |             |

### Default Connections

There are 2 connections **alway** available: `private` and `public`.

By default, the `public` connection represents a _local_ filesystem with the the web-visible root path of the project (the `www` folder) set as the path.

By default, the `private` connection represents a _local_ filesystem with the base path of the project set as the path.



## Logger Service Provider

The `LoggerServiceProvider`, or `charcoal/app/service-provider/logger` provides the following services:

| Service       | Type                       | Description |
| ------------- | -------------------------- | ----------- |
| **logger**    | `\Psr\Log\LoggerInterface` | A PSR-3 compliant logger.

A `\Monolog\Logger` is actually provided by default in charcoal-app.

Also available are the following helpers:

-   `logger/config`
    -   A `\Charcoal\App\Config\LoggerConfig` instance holding the logger configuration.

### Logger Config

| Key               | Type     | Default     | Description |
| ----------------- | -------- | ----------- | ----------- |
| **active**        | `bool`   | `true`      |             |
| **handlers**      | `array`  |             |             |
| **processors**    | `array`  |             |             |

Possible **handlers** are `stream` and `console`.
Possible **processors** are `memory-usage` and `uid`.

```json
{
    "logger": {
        "active": true,
        "handlers": {
            "stream": {},
            "console": {}
        },
        "processors": {
            "memory_usage": {},
            "uid": {}
        }
    }
}
```



## Translator Service Provider

> **External Provider**
> 
> See the [`locomotivemtl/charcoal-translator`][charcoal-translator] for more information on using the translator service.

The `TranslatorServiceProvider`, or `charcoal/translator/service-provider/translator` provides the following services:

-   `translator`

Also available are the following helpers:

-   `locales/config`

### Translator Config

| Key               | Type     | Default     | Description |
| ----------------- | -------- | ----------- | ----------- |
| **locales**       | `array`  |             |             |
| **translator**    | `array`  |             |             |

Or, in JSON format:

```json
"locales": {
    "languages": {
        "de": {},
        "en": {},
        "es": {
            "active": false
        },
        "fr": {}
    },
    "default_language": "fr",
    "fallback_languages": [
        "en", 
        "fr"
    ],
    "auto_detect": true
},
"translator": {
    "loaders": [
        "xliff",
        "json",
        "php"
    ],
    "paths": [
        "translations/",
        "vendor/locomotivemtl/charcoal-app/translations/"
    ],
    "debug": false,
    "cache_dir": "cache/translator",
    "translations": {
        "messages": {
            "de": {
                "hello": "Hallo {{ name }}",
                "goodbye": "Auf Wiedersehen!"
            },
            "en": {
                "hello": "Hello {{ name }}",
                "goodbye": "Goodbye!"
            },
            "es": {
                "hello": "Hallo {{ name }}",
                "goodbye": "Adios!"
            },
            "fr": {
                "hello": "Bonjour {{ name }}",
                "goodbye": "Au revoir!"
            }
        },
        "admin": {
            "fr": {
                "Save": "Enregistrer"
            }
        }
    }
}
```



## View Service Provider

> **External Provider**
> 
> See the [`locomotivemtl/charcoal-view`][charcoal-view] for more information on using the view service.

The `ViewServiceProvider`, or `charcoal/view/service-provider/view` provides the following services:

| Service           | Type                        | Description |
| ----------------- | --------------------------- | ----------- |
| **view**          | `ViewInterface`<sup>1</sup> | A Charcoal view instance.
| **view/renderer** | `Renderer`<sup>2</sup>      | A PSR-7 view / renderer.

1. `\Charcoal\View\ViewInterface`, typically a `\Charcoal\View\GenericView`
2. `\Charcoal\View\Renderer`

Also available are the following helpers:

-   `view/config`
    -   The main View configuration `\Charcoal\View\ViewConfig`
-   `view/engine`
    -   The default View engine (`\Charcoal\View\EngineInterface`)

### View Config

Unlike the other services, the view config is not defined inside this module but in the `charcoal-view` module.

| Key                | Type     | Default       | Description |
| ------------------ | -------- | ------------- | ----------- |
| **paths**          | `array`  | `[]`          | The list of paths where to serach for templates.
| **engines**        | `array`  | `[]`          |             |
| **default_engine** | `string` | `mustache`    |             |

Or, in JSON format:

```json
{
    "view": {
        "paths": [
            "templates/",
            "vendor/locomotivemtl/charcoal-admin/templates/"
        ],
        "engines": {
            "mustache": {}
        },
        "default_engine": "mustache"
    }
}
```



[charcoal-admin]:        https://packagist.org/packages/locomotivemtl/charcoal-admin
[charcoal-app]:          https://packagist.org/packages/locomotivemtl/charcoal-app
[charcoal-cache]:        https://packagist.org/packages/locomotivemtl/charcoal-cache
[charcoal-cms]:          https://packagist.org/packages/locomotivemtl/charcoal-cms
[charcoal-config]:       https://packagist.org/packages/locomotivemtl/charcoal-config
[charcoal-translator]:   https://packagist.org/packages/locomotivemtl/charcoal-translator
[charcoal-view]:         https://packagist.org/packages/locomotivemtl/charcoal-view

[pimple]:                https://packagist.org/packages/pimple/pimple
[slim]:                  https://packagist.org/packages/slim/slim
[stash]:                 https://packagist.org/packages/tedivm/stash
[symfony/translation]:   https://packagist.org/packages/symfony/translation

[psr-1]:  https://www.php-fig.org/psr/psr-1/
[psr-2]:  https://www.php-fig.org/psr/psr-2/
[psr-3]:  https://www.php-fig.org/psr/psr-3/
[psr-4]:  https://www.php-fig.org/psr/psr-4/
[psr-6]:  https://www.php-fig.org/psr/psr-6/
[psr-7]:  https://www.php-fig.org/psr/psr-7/
[psr-11]: https://www.php-fig.org/psr/psr-11/
