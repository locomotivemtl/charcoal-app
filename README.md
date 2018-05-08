Charcoal App
============

[![License][badge-license]][charcoal-app]
[![Latest Stable Version][badge-version]][charcoal-app]
[![Code Quality][badge-scrutinizer]][dev-scrutinizer]
[![Coverage Status][badge-coveralls]][dev-coveralls]
[![SensioLabs Insight][badge-sensiolabs]][dev-sensiolabs]
[![Build Status][badge-travis]][dev-travis]

Charcoal App is a PHP framework to create web applications and APIs using Charcoal components.

The framework is built on top of [Slim 3][gh-slim] and [Pimple][gh-pimple].

---

The Charcoal App is a collection of _modules_, _routes_ (`templates`, `actions` and `scripts`), _handlers_, and _services_ tied together with a _config_, a _service container_, and _service providers_.

The framework features (internally and externally) the following:

- PSR-3 logger
- PSR-6 cache system<sup>†</sup>
- PSR-7 kernel (web, API, CLI)
- PSR-11 container
- Translation layer<sup>†</sup>
- File system layer
- Database layer
- View layer<sup>†</sup>

<small>†  Provided by external Charcoal components</small>



## Table of Contents

-   [Installation](#Installation)
    -   [From Boilerplate](#from-boilerplate)
    -   [Dependencies](#dependencies)
    -   [Recommended Modules](#recommended-modules)
-   [Components](#components)
    -   [Config](#config-component)
    -   [App](#app-compoment)
    -   [Routes and RequestController](#routes-and-requestcontroller)
        -   [Action Request Controller](#action-request-controller)
        -   [Script Request Controller](#script-request-controller)
        -   [Template Request Controller](#template-request-controller)
        -   [Route API](#route-api)
        -   [Routable objects](#routable-objects)
    -   [Middlewares](#middlewares)
    -   [Charcoal Binary](#charcoal-binary)
    -   [PHPUnits Tests](#phpunit-tests)
-   [Service Providers](#service-providers)
-   [Usage](#usage)
-   [Development](#development)
    -   [Development dependencies](#development-dependencies)
    -   [Continuous Integration](#continuous-integration)
    -   [Coding Style](#coding-style)
    -   [Authors](#authors)



## Installation

The preferred (and only supported) way of installing _charcoal-app_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-app
```

### From Boilerplate

This module is intended to be used as the base for a web aplication (such as a website).

For a complete, ready-to-use project, start from the [official boilerplate][gh-charcoal-boilerplate]:

```shell
★ composer create-project locomotivemtl/charcoal-project-boilerplate
```

### Dependencies

#### Required

-   [**PHP 5.6+**](https://php.net): _PHP 7_ is recommended.
-   [**locomotivemtl/charcoal-cache**][charcoal-cache]: Service provider for a PSR-6 compliant caching system, using [Stash][stash].
-   [**locomotivemtl/charcoal-config**][charcoal-config]: Data-objects for configuring the application and designing controllers.
-   [**locomotivemtl/charcoal-factory**][charcoal-factory]: Factory interface for creating providers, processors, and controllers.
-   [**locomotivemtl/charcoal-translator**][charcoal-translator]: Service provider for tools to internationalize your application, using [Symfony Translation][symfony/translation].
-   [**locomotivemtl/charcoal-view**][charcoal-view]: Service provider for a view renderer and templating engine adapters for [Mustache][mustache] and [Twig][twig].
-   [**league/climate**][climate]: Command-line abstraction for designing console commands for your application.
-   [**league/flysystem**][flysystem]: File system abstraction for working with local and remote storage spaces.
-   [**monolog/monolog**][monolog]: PSR-3 compliant client for logging your application's requests, errors, and information.
-   [**pimple/pimple**][pimple]: PSR-11 compliant service container and provider library.
-   [**slim/slim**][slim]: PSR-7 compliant HTTP client and router.

#### PSR

-   [**PSR-3**][psr-3]: Common interface for logging libraries. Fulfilled by Monolog.
-   [**PSR-6**][psr-6]: Common interface for caching libraries. Fulfilled by Stash.
-   [**PSR-7**][psr-7]: Common interface for HTTP messages. Fulfilled by Slim.
-   [**PSR-11**][psr-11]: Common interface for dependency containers. Fulfilled by Pimple.

> 👉 Development dependencies, which are optional when using charcoal-app in a project, are described in the [Development](#development) section of this README file.

Read the `composer.json` file for more details on dependencies.

### Recommended Modules

In addition to the above dependencies, here's a list of recommended modules that can be added to a project.

-   [**locomotivemtl/charcoal-email**][charcoal-email]: Service provider for email management, using [PHPMailer][phpmailer] (templating, queuing, sending, tracking).
-   [**locomotivemtl/charcoal-cms**][charcoal-cms]: Pre-designed models and basic utilities for content management (pages, news, events).
-   [**locomotivemtl/charcoal-admin**][charcoal-admin]: Administration interface for your Charcoal applications (add/edit/delete objects, file manager).

> Using the `charcoal-project-boilerplate` is really the recommended way of making sure a "full" Charcoal application is set up.
> 
> To install:
>
> ```shell
> ★ composer create-project locomotivemtl/charcoal-project-boilerplate
> ```



## Components

The main components of the Charcoal App are:

-   [Config](#config-component)
-   [App](#app-compoment)
-   ~~[Module](#module-component)~~
-   [Routes & Request Controllers](#routes--request-controllers)
    -   [Action](#action-request-controller)
    -   [Script](#script-request-controller)
    -   [Template](#template-request-controller)
    -   [Route API](#route-api)
-   [Routable Objects](#routable-objects)
-   [Charcoal Binary](#charcoal-binary)
-   [PHPUnit Tests](#phpunit-tests)

Learn more about [components](docs/components.md).



## Service Providers

Dependencies and extensions are handled by a dependency container, using [Pimple][pimple], which can be defined via _service providers_ (`Pimple\ServiceProviderInterface`).

#### Included Providers

The Charcoal App comes with several providers out of the box. All of these are within the `Charcoal\App\ServiceProvider` namespace:

-   [`AppServiceProvider`](docs/providers.md#app-service-provider)
-   [`DatabaseServicePovider`](docs/providers.md#database-service-provider)
-   [`FilesystemServiceProvider`](docs/providers.md#filesystem-service-provider)
-   [`LoggerServiceProvider`](docs/providers.md#logger-service-provider)

#### External Providers

The Charcoal App requires a few providers from independent components. The following use their own namespace and are automatically injected via the `AppServiceProvider`:

-   [`CacheServiceProvider`](docs/providers.md#cache-service-provider)
-   [`TranslatorServiceProvider`](docs/providers.md#translator-service-provider)
-   [`ViewServiceProvider`](docs/providers.md#view-service-provider)

Learn more about [service providers](docs/providers.md).



## Usage

Typical Front-Controller ([`www/index.php`](www/index.php)):

```php
use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

include '../vendor/autoload.php';

$config = new AppConfig();
$config->addFile(__DIR__.'/../config/config.php');
$config->set('ROOT', dirname(__DIR__) . '/');

// Create container and configure it (with charcoal-config)
$container = new AppContainer([
    'settings' => [
        'displayErrorDetails' => true
    ],
    'config' => $config
]);

// Charcoal / Slim is the main app
$app = App::instance($container);
$app->run();
```

For a complete project example using `charcoal-app`, see the [charcoal-project-boilerplate][gh-charcoal-boilerplate].



## Development

To install the development environment:

```shell
$ composer install
```

To run the scripts (phplint, phpcs, and phpunit):

```shell
$ composer test
```



### API Documentation

-   The auto-generated `phpDocumentor` API documentation is available at:  
    [https://locomotivemtl.github.io/charcoal-app/docs/master/](https://locomotivemtl.github.io/charcoal-app/docs/master/)
-   The auto-generated `apigen` API documentation is available at:  
    [https://codedoc.pub/locomotivemtl/charcoal-app/master/](https://codedoc.pub/locomotivemtl/charcoal-app/master/index.html)



### Development Dependencies

-   [php-coveralls/php-coveralls][phpcov]
-   [phpunit/phpunit][phpunit]
-   [squizlabs/php_codesniffer][phpcs]



### Coding Style

The charcoal-cache module follows the Charcoal coding-style:

-   [_PSR-1_][psr-1]
-   [_PSR-2_][psr-2]
-   [_PSR-4_][psr-4], autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   [phpcs.xml.dist](phpcs.xml.dist) and [.editorconfig](.editorconfig) for coding standards.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.



## Credits

-   [Mathieu Ducharme](https://github.com/mducharme)
-   [Chauncey McAskill](https://github.com/mcaskill)
-   [Benjamin Roch](https://github.com/beneroch)
-   [Locomotive](https://locomotive.ca/)



## License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.



[charcoal-admin]:        https://packagist.org/packages/locomotivemtl/charcoal-admin
[charcoal-app]:          https://packagist.org/packages/locomotivemtl/charcoal-app
[charcoal-cache]:        https://packagist.org/packages/locomotivemtl/charcoal-cache
[charcoal-cms]:          https://packagist.org/packages/locomotivemtl/charcoal-cms
[charcoal-config]:       https://packagist.org/packages/locomotivemtl/charcoal-config
[charcoal-email]:        https://packagist.org/packages/locomotivemtl/charcoal-email
[charcoal-factory]:      https://packagist.org/packages/locomotivemtl/charcoal-factory
[charcoal-translator]:   https://packagist.org/packages/locomotivemtl/charcoal-translator
[charcoal-view]:         https://packagist.org/packages/locomotivemtl/charcoal-view

[gh-slim]:                  https://github.com/slimphp/Slim/tree/3.x
[gh-pimple]:                https://github.com/silexphp/Pimple
[gh-charcoal-boilerplate]:  https://github.com/locomotivemtl/charcoal-project-boilerplate

[dev-scrutinizer]:    https://scrutinizer-ci.com/g/locomotivemtl/charcoal-app/
[dev-coveralls]:      https://coveralls.io/r/locomotivemtl/charcoal-app
[dev-sensiolabs]:     https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a
[dev-travis]:         https://travis-ci.org/locomotivemtl/charcoal-app

[badge-license]:      https://img.shields.io/packagist/l/locomotivemtl/charcoal-app.svg?style=flat-square
[badge-version]:      https://img.shields.io/packagist/v/locomotivemtl/charcoal-app.svg?style=flat-square
[badge-scrutinizer]:  https://img.shields.io/scrutinizer/g/locomotivemtl/charcoal-app.svg?style=flat-square
[badge-coveralls]:    https://img.shields.io/coveralls/locomotivemtl/charcoal-app.svg?style=flat-square
[badge-sensiolabs]:   https://img.shields.io/sensiolabs/i/533b5796-7e69-42a7-a046-71342146308a.svg?style=flat-square
[badge-travis]:       https://img.shields.io/travis/locomotivemtl/charcoal-app.svg?style=flat-square

[climate]:               https://packagist.org/packages/league/climate
[fastroute]:             https://packagist.org/packages/nikic/fast-route
[flysystem]:             https://packagist.org/packages/league/flysystem
[monolog]:               https://packagist.org/packages/monolog/monolog
[mustache]:              https://packagist.org/packages/mustache/mustache
[phpmailer]:             https://packagist.org/packages/phpmailer/phpmailer
[phpunit]:               https://packagist.org/packages/phpunit/phpunit
[phpcs]:                 https://packagist.org/packages/squizlabs/php_codesniffer
[phpcov]:                https://packagist.org/packages/php-coveralls/php-coveralls
[pimple]:                https://packagist.org/packages/pimple/pimple
[slim]:                  https://packagist.org/packages/slim/slim
[stash]:                 https://packagist.org/packages/tedivm/stash
[symfony/translation]:   https://packagist.org/packages/symfony/translation
[twig]:                  https://packagist.org/packages/twig/twig

[psr-1]:  https://www.php-fig.org/psr/psr-1/
[psr-2]:  https://www.php-fig.org/psr/psr-2/
[psr-3]:  https://www.php-fig.org/psr/psr-3/
[psr-4]:  https://www.php-fig.org/psr/psr-4/
[psr-6]:  https://www.php-fig.org/psr/psr-6/
[psr-7]:  https://www.php-fig.org/psr/psr-7/
[psr-11]: https://www.php-fig.org/psr/psr-11/
