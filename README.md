Charcoal App
============

`Charcoal\App` is a framework to create _Charcoal_ applications with **Slim 3**. It is actually a small layer on top of Slim to load the proper routes / controllers from a configuration file, as well as setting up various service providers (for logger, cache, database and translation) that make up an application.

The request is then handled by one of the 3 types of route (or _request controller_): `Action`, `Script` or `Template`.


# Table of contents

- [How to install](#how-to-install)
	- [Dependencies](#dependencies)
	- [Recommended modules](#recommended-modules)
- [Components](#components)
  - [Config](#config-component)
  - [App](#app-compoment)
  - [Module](#module-component)
  - [Routes and RequestController](#routes-and-requestcontroller)
    - [Route API](#route-api)
    - Action
    - Script
    - Template
  - [Routable objects](#routable-objects)
    - [The routable callback](#the-routable-callback)
  - [Middleware](#middleware)
  - [Charcoal Binary](#charcoal-binary)
- Basic Services
- [Service Providers](#service-providers)
  - [App Service Provider](#app-service-provider)
  - [Cache Service Provider](#cache-service-provider)
  - [Database Service Provider](#database-service-provider)
  - [Logger Service Provider](#logger-service-provider)
  - [Translator Service Provider](#translator-service-provider)
  - [View Service Provider](#view-service-provider)
- [Usage](#usage)
- [Development](#development)
  - [Development dependencies](#development-dependencies)
  - [Continuous Integration](#continuous-integration)
  - [Coding Style](#coding-style)
  - [Authors](#authors)
  - [Changelog](#changelog)

# How to install

The preferred (and only supported) way of installing _charcoal-app_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-app
```

A PSR-4 compliant autoloader

## Dependencies

- [`PHP 5.5+`](http://php.net)
	- Older versions of PHP are deprecated, therefore not supported for charcoal-app. PHP 5.6 + is recommendend.
- [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
	-  The basic configuration system and config container.
	-  Also provides the base `AbstractEntity` data container.
- [`locomotivemtl/charcoal-factory`](https://github.com/locomotivemtl/charcoal-factory)
	- Dynamic object creation.
	- Factories are provided for Action, Module, Routable, Route, Script, ServiceProvider, Template and Widget
- [`locomotivemtl/charcoal-view`](https://github.com/locomotivemtl/charcoal-view)
	- Template controllers will typically load a _View_ object  (or a _Renderer_, for PSR7 / Slim compatibility) and render a template.
	- An engine should also be installed. This can be:
		- Mustache (default, recommended)
		- Twig
		- or simple PHP templates.
- [`slim/slim`](https://github.com/slimphp/Slim)
	- Slim is a PSR-7 compliant micro-framework.
	- It provides the main app, container and router.
	- Its dependencies are:
		-  [`nikic/fast-route`](https://github.com/nikic/FastRoute)
		-  `pimple/pimple`
		-  [`psr/http-message`]((http://www.php-fig.org/psr/psr-7/))
- [`pimple/pimple`](http://pimple.sensiolabs.org/)
  - Dependency injection container.
  - Actually provided by `slim/slim`.
- `monolog/monolog`
	- Monolog is a PSR-3 compliant logger.
	- Monolog is used as main logger to fulfills PSR3 dependencies all-around.
- [`tedivm/stash`](https://github.com/tedious/Stash)
  - Stash is a PSR-6 compliant cache system.
  - Cache greatly speeds up an application. A driver must be configured:
  	- Supported drivers are `memcache, `redis`, `db` (sqlite), `file`, `memory` or `noop`
  	- Recommended drivers are `memcache` and `redis`.

> 👉 Development dependencies, which are optional when using charcoal-app in a project, are described in the [Development](#development) section of this README file.

Read the `composer.json` file for more details on dependencies.

## Recommended modules

In addition to the above dependencies, here's a list of recommended modules that can be added to a project.

- [locomotivemtl/charcoal-email](https://github.com/locomotivemtl/charcoal-email)
	- Easily set up, send, queue or track emails.
	- Integrates with `charcoal-app` with _Service Provider_.
	- Can use standard Charcoal Template as _View_.
	- Uses `phpmailer` to actually send the mail.
- [locomotivemtl/charcoal-cms](https://github.com/locomotivemtl/charcoal-cms)
	- Base objects (Section, Text, Document, etc.) for a _CMS_ style of website.
	- Is made to be used with charcoal-admin
- [locomotivemtl/charcoal-admin](https://github.com/locomotivemtl/charcoal-admin)
	- A modern, responsive backend for Charcoal projects.
	- Especially made for Charcoal _models_ / _objects_.
	- A good example of `charcoal-app` / mustache templates usage.

For a complete, ready-to-user project, use the `charcoal-project-boilerplate`:

```shell
★ composer create-project locomotivemtl/charcoal-project-boilerplate
```

# Components

The main components of charcoal-app are:


- [Config](#config-component)
- [App](#app-compoment)
- [Module](#module-component)
- [Routes and RequestController](#routes-and-requestcontroller)
- [Route API](#route-api)
	- Action
	- Script
	- Template
- [Routable objects](#routable-objects)
- [The routable callback](#the-routable-callback)
- [Middleware](#middleware)
- [Charcoal Binary](#charcoal-binary)

## Config component

At the core of a _Charcoal application_ is a highy customizable **Config** system, provided by [charcoal-config](https://github.com/locomotivemtl/charcoal-admin).

Typically, the configuration should load a file located in `config/config.php` in a `AppConfig` object. This file might load other, specialized, config file (PHP, json or ini files).

> It is recommended to keep a separate _config_ file for all of your different app modules. Compartmentalized config sections are easier to maintain and understand.

## App component

The App component is based on [Slim](https://github.com/slimphp/Slim).

> **What is Slim?**
>
> At its core, Slim is a dispatcher that receives an HTTP request, invokes an appropriate callback routine, and returns an HTTP response.

- The *App* loads the root onfiguration.
	- **App**: _implements_ `\Charcoal\App\App`
	- **Config**: `\Charcoal\App\AppConfig`
		- The `AppConfig` expects a key called `modules`
			- Each modules have an ident and a sub-configuration (`ModuleConfig`)
	- **Container**: Dependencies are expected to be in a `Pimple` container

- The *App* has one method: `setup()` wich:
	- Accepts a `\Slim\App` as a parameter.
	- Instanciate a `ModuleManager` which:
		- Loop all `modules` from the `AppConfiguration` and create new *Modules* according to the configuration.
		- (The Module creation is done statically via it's `setup()` abstract method)

> 👉 The `App` concept is entirely optional. Modules could be loaded without one.

### Base App Configuration

| Key                  | Type      | Default | Description |
| -------------------- | --------- | ------- | ----------- |
| **base_path**        | `array`   | `[]`    |             |
| **base_url**         | `array`   | `[]`    |             |
| **ROOT**             | `array`   | `[]`    | An alias of `base_path`. |
| **timezone**         | `string`  | `"UTC"` | The current timezone. |


### Module & App configuration

`\Charcoal\App\AppConfig` API:

| Key                  | Type      | Default | Description |
| -------------------- | --------- | ------- | ----------- |
| **middlewares**      | `array`   | `[]`    | ...         |
| **modules**          | `array`   | `[]`    | ...         |
| **routables**        | `array`   | `[]`    | ...         |
| **routes**           | `array`   | `[]`    | ...         |
| **service_providers**| `array`   | `[]`    | ...         |


> The main app can be seen, in a way, as the "default module".

### Basic service configuration

| Key                  | Type      | Default | Description |
| -------------------- | --------- | ------- | ----------- |
| **cache**            | `array`   | `null`  |
| **databases**        | `array`   | `[]`    | An array of `DatabaseConfig`
| **default_database** | `string`  | `""`    |
| **email**            | `array`   | `[]`    | The email (default from and SMTP options) configuration. See [EmailConfig](https://github.com/locomotivemtl/charcoal-email) |
| **logger**           | `array`   | `null`  | The logger service configuration
| **translator**       | `array`   | `null`  |
| **view**             | `array`   | `null`  | The default view configuration (default engine and path settings). See [ViewConfig](https://github.com/locomotivemtl/charcoal-view). I|

## Module component

- A *Module* loads its configuration from the root config
	- **Module**: _implements_ `Charcoal\App\ModuleInterface`
	- **Config**: `\Charcoal\App\ModuleConfig`
		- The `ModuleConfig

- A *Module* requires:
	- A parent **Container**
	- A `\Slim\App`

- A *Module* defines:
	- **Routes**: which defines a path to load and a `RequestController` configuration.
	- **Middlewares**: which are TBD.

## Routes and RequestController

All routes are actually handled by the *Slim* app. Charcoal Routes are just *definition* of a route:

- An identifier, which typically matches the controller.
- A RouteConfig structure, which contains:
	- The `type` of  `RequestController`. This can be:
		- `Action`
		- `Script` (_Scripts_ can only be ran from the CLI.)
		- `Template`
	- The `controller` ident

### Route API

> 👉 Slim's routing is actually provided by [FastRoute](https://github.com/nikic/FastRoute)

**Common route configuration**

| Key             | Type       | Default     | Description |
| --------------- | ---------- | ----------- | ----------- |
| **ident**       | `string`   | `null`      | Route identifier. |
| **methods**     | `string[]` | `[ 'GET' ]` | The HTTP methods to wthich this route resolve to. Ex: `['GET', 'POST', 'PUT', 'DELETE']` |
| **controller**  | `string`   | `null`      | Controller identifier. Will be guessed from the _ident_ when `null`. |
| **lang**        | `string`   | `null`      | The current language. |
| **group**       | `string`   | `null`      | The route group, if any. |


There are 3 types of `Route`:

- `ActionRoute`: typically executes an action (return JSON) from a _POST_ request.
- `ScriptRoute`: typically ran from the CLI interface.
- `TemplateRoute`: typically  load a template from a _GET_ request. "A Web page".

**Action specific configuration**

| Key               | Type     | Default      | Description |
| ----------------- | -------- | ------------ | ----------- |
| **action_data**   | `array`  | `[]`         | Extra / custom action data. |

By default, _actions_ are map to the `'POST'` methods.

**Script specific configuration**

| Key               | Type     | Default      | Description |
| ----------------- | -------- | ------------ | ----------- |
| **script_data**   | `array`  | `[]`         | Extra / custom script data. |

**Template specific configuration**

| Key               | Type     | Default      | Description |
| ----------------- | -------- | ------------ | ----------- |
| **template**      | `string` | `null`       | The template _ident_ to display. |
| **engine**        | `string` | `'mustache'` | The template _engine_ type. Default Charcoal view engines are `mustache`, `php` and `php-mustache`. |
| **template_data** | `array`  | `[]`         | Extra / custom template data. |

#### Defining a default route.

To set the "default" route, simply map a route to "/".

```json
{
	"routes": {
		"templates":{
			"/": {
				"redirect":"home"
			},
			"home":{
				"template": "acme/home"
			}
		},
		"actions":{
			"publish":{
				"controller":"acme/action/blog/publish",
			}
		},
		"scripts":{
			"foo":{
				"controller":"acme/script/foo"
			}
		}
	}
}
```

## Routable objects

Routes are great to match URL path to template controller or action controller, but needs to be defined in the `AppConfig` container.

Routables, on the other hand, are dynamic objects (typically, Charcoal Model objects that implements the `Charcoal\App\Routable\RoutableInterface`) whose _route path_ is typically defined from a dynamic property (and stored in a database).

### The routable callback

The `RoutableInterface` / `RoutableTrait` classes have one abstract method: `handle_route($path, $request, $response)` which must be implemented in the routable class.

This method should:

- Check the path to know if it should respond
	- Typically, this means checking the _path_ parameter against the database to load a matching object.
	- But really, it could be anything...
- Return a `callable` object that will handle the route if it matches
- Return `null` if no match

The returned callable signature should be:
`function(RequestInterface $request, ResponseInterface $response)` and returns a `ResponseInterface`

Routables are called last (only if no explicit routes match fisrt). If no routables return a callable, then a 404 will be sent. (Slim's `NotFoundHandler`).

## Middleware

Middleware is not yet implemented in `Charcoal\App`. The plan is to use the PSR7-middleware system, which is a callable with the signature:

```
use \Psr\Http\Message\RequestInterface as RequestInterface;
use \Psr\Http\Message\ResponseInterfac as ResponseInterface;

middleware(RequestInterface $request, ResponseInterface $response) : ResponseInterface
```

## Charcoal Binary

As previously mentionned, `Script` routes are only available to run from the CLI. A script loader is provided in `bin/charcoal`. It will be installed, with composer, in `vendor/bin/charcoal`.

To view available commands:

```shell
★ ./vendor/bin/charcoal
```

To enable a script, simply add a _script route_ to your configuration:

```json
{
	"routes":{
		"scripts":{
			"acme/foo":{
				"controller":"acme/script/foo-bar"
			}
		}
	}
}
```

In this example, calling the script with:

```shell
★ ./vendor/bin/charcoal acme/foo
```

would call the `\Acme\Script\FooBarScript` class, which must implement the `\Charcoal\App\Script\ScriptInterface`. A typical script only needs to reimplement the `run()` method, and the `defaultArguments()` method if required:

```php

namespace Acme\Script;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Charcoal\App\Script\AbstractScript;

class FooBarScript extends AbstractScript
{
	/**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
	public function run(RequestInterface $request, ResponseInterface $response)
	{
		$this->climate()->out('This script does nothing.');
		return $response;
	}
}
```

## Configuration examples

Example of a module configuration:

```json
{
	"routes": {
		"templates": {
			"foo/bar": {},
			"foo/baz/{:id}": {
				"controller": "foo/baz",
				"methods": [ "GET", "POST" ]
			}
		},
		"default_template": "foo_bar",
		"actions": {
			"foo/bar": {}
		}
	},
	"routables": {
		"charcoal/cms/news": {}
	},
	"middlewares": {}
}
```


# Service Providers

Dependencies are handled with a `Pimple` dependency Container. There are various _Service Providers_ available inside `charcoal-app`:

- [`AppServiceProvider`](#app-service-provider)
- [`CacheServiceProvider`](#cache-service-provider)
- [`DatabaseServicePovider`](#database-service-provider)
- [`LoggerServiceProvider`](#logger-service-provider)
- [`TranslatorServiceProvider`](#translator-service-provider)
- [`ViewServiceProvider`](#view-service-provider)

All providers expect the DI Container to provide `config` object, which should hold the main project configuration in a `ConfigInterface` instance.

## Basic services

Dependencies are handled with a `Pimple` dependency Container.

Basic "App" services are:

- `config`
  - A `\Charcoal\App\AppConfig` instance.
- `logger`
  - A `\Psr\Log\Logger` instance.
  - Provided by _Monolog_.
  - Configured by `config['logger']`
- `cache`
  - A `\Stash\Pool` instance.
  - Configured by `config['cache']`
- `view`
	- A `Charcoal\View\ViewInterface` instance
	- Typically a `\Charcoal\View\GenericView` object.
	- Configured by `config['view']`
- `database`
  - The default _PDO_ database.
  - From a pool of database, available through `databases`.
  - Configured by `config['databases']` and `config['default_database']`.
- `translator`
  - To do.

## App Service Provider

The `AppServiceProvider`, or `charcoal/app/service-provider/app` provides the following services:

| Service       | Type                | Description |
| ------------- | ------------------- | ----------- |
| **notFoundHandler**  | `callback`   | For 404 (Not Found) URLs. Slim requirement.
| **errorHandler**     | `callback`   | For 500 (Error) URLs. Slim requirement. |
| **action/factory**   | `ActionFactory`<sup>1</sup> | To create actions. |
| **script/factory**   | `ScriptFactory`<sup>2</sup> | To create templates. |
| **template/factory** | `TemplateFactory`<sup>3</sup> | To create templates. |
| **widget/factory**   | `WidgetFactory`<sup>4</sup> | To create widgets.

<sup>1</sup> `\Charcoal\App\Action\ActionFactory`.<br>
<sup>2</sup> `\Charcoal\App\Script\ScriptFactory`.<br>
<sup>3</sup> `\Charcoal\App\Template\TemplateFatory`.<br>
<sup>4</sup> `\Charcoal\App\Widget\WidgetFactory`.<br>
<small>All factories are `\Charcoal\Factory\FactoryInterface`</small>


## Cache Service Provider

The `CacheServiceProvider`, or `charcoal/app/service-provider/cache` provides the following servicers:

| Service       | Type                | Description |
| ------------- | ------------------- | ----------- |
| **cache**     | `\Stash\Pool`       | PSR-6-compliant cache pool (stash). |


Also available are the following helpers:

| Helper Service    | Type                | Description |
| :---------------- | ------------------- | ----------- |
| **cache/config**  | `CacheConfig`<sup>1</sup> | Cache configuration.
| **cache/available-drivers** | `array`   | Available drivers on the system.
| **cache/drivers** | `\Pimple\Contianer` | Map of all the available `\Stash\Driver` instances. |
| **cache/driver**  | `\Stash\Driver`     | The default `\Stash\Driver`. |


### Cache config

| Key               | Type     | Default       | Description |
| ----------------- | -------- | ------------- | ----------- |
| **types**         | `array`  | `['memory']`  | The cache types to attempt to use, in order. |
| **default_ttl**    | `int`    | `0`           | Default _time-to-live_, in seconds. |
| **prefix**        | `string` | `charcoal`         | The cache prefix, or namespace. |

A full example, in JSON format:

```json
{
	"cache":{
		"types": ["memcache", "memory"],
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
| **databases** | `\Pimple\Container` | A map (container) of all the available PDO instances. |

Also available are the following helpers:

| Helper Service       | Type                | Description |
| -------------------- | ------------------- | ----------- |
| **database/config**  | `DatabaseConfig`<sup>1</sup> | Default database config container.
| **databases/config** | `\Pimple\Container` | A map (container) of all the available PDO instances. |

<sup>1</sup> `\Charcoal\App\Config\DatabaseConfig`

### Database config

The databases are configured with the following options:

| Key                  | Type     | Default       | Description |
| -------------------- | -------- | ------------- | ----------- |
| **databases**        | `array`  |               |             |
| **default_database** | `string` |               |             |

The **database** config is as follow:

| Key               | Type     | Default       | Description |
| ----------------- | -------- | ------------- | ----------- |
| **type**          | `string` | `mysql`       | The database driver type.
| **hostname**      | `string` |
| **username**      | `string` |
| **password**      | `string` |
| **database**      | `string` |
| **disable_utf8**  | `bool`   |

Or, in JSON format:

```json
{
	"databases":{
		"foobar":{
			"type":"mysql",
			"hostname":"dbserver.example.com",
			"username":"dbuser",
			"password":"dbpassword",
			"disable_utf8":false
		}
	},
	"default_database":"foobar"
}
```

## Logger Service Provider

The `LoggerServiceProvider`, or `charcoal/app/service-provider/logger` provides the following services:

| Service       | Type                | Description |
| ------------- | ------------------- | ----------- |
| **logger**    | `\Psr\Log\LoggerInterface` | A PSR-3 compliant logger.

A `\Monolog\Logger` is actually provided in charcoal-app.

Also available are the following helpers:

- `logger/config`
	+ A `\Charcoal\App\Config\LoggerConfig` instance holding the logger configuration.

### Logger config

| Key               | Type     | Default       | Description |
| ----------------- | -------- | ------------- | ----------- |
| **active**        | `bool`   | `true`
| **handlers**      | `array`  |
| **processors**    | `array`  |

Possible **handlers** are `stream` and `console`.
Possible **processors** are `memory-usage` and `uid`.

```json
{
	"logger":{
		"active":true,
		"handlers":{
			"stream":{},
			"console":{}
		},
		"processors":{
			"memory_usage":{},
			"uid":{}
		}
	}
}
```

## Translator Service Provider

The `TranslatorServiceProvider`, or `charcoal/app/service-provider/translator` provides the following services:

- `translator`
	+ Todo

Also available are the following helpers:

- `translator/config`

### Translator config

| Key               | Type     | Default       | Description |
| ----------------- | -------- | ------------- | ----------- |
| **active**        | `bool`   | `true`
| **types**         | `array`  |
| **locales**       | `array`  |
| **translations**  | `array`  |

Or, in JSON format:

```json
{
	"translator": {
		"active": true,
		"types": [],
		"locales": {
			"repositories":[],
			"languages":{
				"en":{},
				"fr":{}
			},
			"default_language":"",
			"fallback_languages":["en"]
		},
		"translations": {
			"paths": [],
			"messages": []
		}

	}
}
```

## View Service Provider

The `ViewServiceProvider`, or `charcoal/app/service-provider/view` provides the following services:

| Service       | Type                | Description |
| ------------- | ------------------- | ----------- |
| **view**      | `ViewInterface`<sup>1</sup> | A Charcoal view instance.
| **view/renderer** | `Renderer`<sup>2</sup> | A PSR-7 view / renderer. |

<sup>1</sup> `\Charcoal\View\ViewInterface`, typically a `\Charcoal\View\GenericView`.<br>
<sup>2</sup> `\Charcoal\View\Renderer`.<br>

Also available are the following helpers:

- `view/config`
	+ The main View configuration `\Charcoal\View\ViewConfig`
- `view/engine`
	+ The default View engine (`\Charcoal\View\EngineInterface`)

### View Config

Unlike the other services, the view config is not defined inside this module but in the `charcoal-view` module.

| Key                | Type     | Default       | Description |
| ------------------ | -------- | ------------- | ----------- |
| **paths**          | `array`  | `[]`          | The list of paths where to serach for templates. |
| **engines**        | `array`  | `[]`          |
| **default_engine** | `string` | `mustache`    |

Or, in JSON format:

```json
{
	"view": {
		"paths":[
			"templates/",
			"vendor/locomotivemtl/charcoal-admin/templates/"
		],
		"engines":{
			"mustache":{}
		},
		"default_engine":"mustache"
	}
}
```

# Usage

Typical Front-Controller (`index.php`):

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

For a complete project example using `charcoal-app`, see the [charcoal-project-boilerplate](https://github.com/locomotivemtl/charcoal-project-boilerplate).


# Development

To install the development environment:

```shell
$ composer install --prefer-source
```

## Development dependencies

- `phpunit/phpunit`
- `squizlabs/php_codesniffer`
- `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-app) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-app.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-app) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-app/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-app/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-app/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-app) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-app/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-app?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a/mini.png)](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | Another code quality checker, focused on PHP. |

## Coding Style

The Charcoal-App module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
- [_phpDocumentor_](http://phpdoc.org/) comments.
- Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>
- Chauncey McAskill <chauncey@locomotive.ca>
- Benjamin Roch <benjamin@locomotive.ca>

## Changelog

### 0.1

_2016-03-09_

- Initial release
