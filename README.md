Charcoal App
============

`Charcoal\App` is a framework to create _Charcoal_ applications with **Slim 3**. It is actually a small layer on top of Slim to load the proper routes / controllers and middlewares from a configuration file.

[![Build Status](https://travis-ci.org/locomotivemtl/charcoal-app.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-app)

# How to install

The preferred (and only supported) way of installing _charcoal-app_ is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-app
```

## Dependencies

- [`PHP 5.5+`](http://php.net)
	- Older versions of PHP are deprecated, therefore not supported for charcoal-app.
- [`locomotivemtl/charcoal-config`](https://github.com/locomotivemtl/charcoal-config)
	-  The basic configuration container.
- [`locomotivemtl/charcoal-factory`](https://github.com/locomotivemtl/charcoal-factory)
	- Dynamic object creation.
- [`locomotivemtl/charcoal-view`](https://github.com/locomotivemtl/charcoal-view)
	- Template controllers will typically load a _View_ object  (or a _Renderer_, for PSR7 / Slim compatibility) and render a template.
	- This brings a dependency on [`mustache/mustache`](https://github.com/bobthecow/mustache.php).

- [`slim/slim`](https://github.com/slimphp/Slim)
	- The main app, container and router are provided by Slim.
	- Its dependencies are:
		-  [`pimple/pimple`](http://pimple.sensiolabs.org/)
		-  [`psr/http-message`]((http://www.php-fig.org/psr/psr-7/))
		-  [`nikic/fast-route`](https://github.com/nikic/FastRoute)
- `pimple/pimple`
  - Dependency injection container.
  - Actually provided by `slim/slim`.
- `monolog/monolog`
	- Monolog is used as main logger to fulfills PSR3 dependencies all-around.
- `tedivm/stash`
  - Stash is a cache system.
  - As of _2016-01-26_, is not yet PSR6 compliant (released versions).

> 👉 Development dependencies, which are optional when using charcoal-app in a project, are described in the [Development](#development) section of this README file.

### The PSR-7 standard (http messages)

Just like _Slim_, charcoal-app is built around the [`psr-7`](http://www.php-fig.org/psr/psr-7/) standard.

_Charcoal Actions_ are typically ran either by the `run()` method or by inkoking an action instance (with the `__invoke()` magic method). This method takes a _RequestInterface_ and a _ResponseInterface_ as parameters and returns a _ResponseInterface_.

Similarly, when a `Charcoal\View\Renderer` is used as a renderer (instead of a plain view), the `render()` method accepts (and returns) a _ResponseInterface_ object.

# Components

The main components of charcoal-app are _App_, _Module_, _Route_ (and _Routable_ objects), _RequestController_, _Middleware_ and the _Binary (Charcoal Script)_.

## App

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

### App configuration

`\Charcoal\App\AppConfig` API:

| Key         | Type             | Default | Description |
| ----------- | ---------------- | ------- | ----------- |
| **routes**  | `RouteConfig[]`  | `[]`    | ...         |
| **modules** | `ModuleConfig[]` | `[]`    | ...         |

## Module

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

Common route configuration:

| Key             | Type       | Default     | Description |
| --------------- | ---------- | ----------- | ----------- |
| **ident**       | `string`   | `null`      | Route identifier. |
| **methods**     | `string[]` | `[ 'GET' ]` | The HTTP methods to wthich this route resolve to. Ex: `['GET', 'POST', 'PUT', 'DELETE']` |
| **controller**  | `string`   | `null`      | Controller identifier. Will be guessed from the _ident_ when `null`. |
| **lang**        | `string`   | `null`      | The current language. |
| **group**       | `string`   | `null`      | |

Template specific configuration:

| Key               | Type     | Default      | Description |
| ----------------- | -------- | ------------ | ----------- |
| **template**      | `string` | `null`       | The template _ident_ to display. |
| **engine**        | `string` | `'mustache'` | The template _engine_ type. Default Charcoal view engines are `mustache`, `php` and `php-mustache`. |
| **template_data** | `array`  | `[]`         | Extra / custom template data. |

There are 3 types of `Route`:

- `ActionRoute`: typically executes an action (return JSON) from a _POST_ request.
- `ScriptRoute`: typically ran from the CLI interface.
- `TemplateRoute`: typically  load a template from a _GET_ request. "A Web page".

#### Defining a default route.

To set the "default" rout, simply map a route to "/".

```json
{
	"routes": {
		"/": {
			"template": "acme/home"
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

## Binary (Charcoal script)

As previously mentionned, `Script` routes are only available to run from the CLI. A script loader is provided in `bin/charcoal`. It will be installed, with composer, in `vendor/bin/charcoal`.

## Summary

- An _App_ is a collection of _Modules_, which are a collection of _Routes_ and _Middlewares_.
- _Routes_ are just (config) definitions that match a path to a _RequestController_
	- There are 3 types of _RequestController_: _Actions_, _Scripts_ and _Templates_.

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
# Basic services

Dependencies are handled with a `Pimple` dependency Container.

Basic "App" services are:

- `config`
- `logger`
- `cache`
- `view`
- `database`
- `translator`

The next section, service providers, explain in more details the various available services.

# Service Providers

Dependencies are handled with a `Pimple` dependency Container. There are various _Service Providers_ available inside `charcoal-app`:

- `AppServiceProvider`
- `CacheServiceProvider`
- `DatabaseServicePovider`
- `LoggerServiceProvider`
- `TranslatorServiceProvider`
- `ViewServiceProvider`

All providers expect the DI Container to provider `config` object, which should hold the main project configuration in a `ConfigInterface` instance.

## App Service Provider

The `AppServiceProvider` provides the following services:
- `notFoundHandler`
  - A `callback` for 404 (not found) URLs.
- `errorHandler`
  - A `callback` for 500 (error) URLs.

## Cache Service Provider

The `CacheServiceProvider` provides the following servicers:

- `cache`
  - A `\Stash\Pool` (cache) instance.

Also available are the following helpers:

- `cache/config`
	- A `\Charcoal\App\Config\CacheConfig` instance holding all configuration keys related to caching.
- `cache/available-drivers`
  - An `array` of all the available Stash drivers on the system.
- `cache/drivers`
  - A `\Pimple\Container` of all the avaialble `Stash\Driver` instances.
- `cache/driver`
  - The default `\Stash\Driver`.

> 👉 Stash 1.0 will support PSR-6. For now, `charcoal` is dependent on stash, not the psr6 standard.

## Database Service Provider

The `DatabaseServiceProvider` provides the following services:

- `database`
	- The default database, as a `\PDO` instance.
- `databases`
  - A `\Pimple\Container` of all the available `\PDO` database instances.

## Logger Service Provider

The `LoggerServiceProvider` provides the following services:

- `logger`

Also available are the following helpers:

- `logger/config`


## Translator Service Provider

## View Service Provider

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

It is also possible to bypass the `Charcoal\App` totally and simply instanciate each modules manually:

```php
// ...
$slim = new \Slim\App($container);

\Charcoal\Admin\Module::setup($slim);
\Charcoal\Messaging\Module::setup($slim);
\Foobar\Module::setup($slim);

$slim->run();
```
This achieves the same result, excepts the *Modules* were not loaded from the root configuration but hard-coded.

Without Module to handle routes and middlewares:

```php
// ...
$slim = new \Slim\App($container);

$container['controller_loader'] = function($c) {

};

// Add middleware manually
// $slim->add('\Foobar\Middleware\Foo');

$slim->get('/', function($request, $response, $args) {
	$container = $this->getContainer();
	$request_controller = $container['controller_loader']->get('/');
	return $request_controller($request, $response, $args);
});

$slim->post('/', function() {

});
```

# Development

To install the development environment:

```shell
$ npm install
$ composer install
```

## Development dependencies

- `npm`
- `grunt` (install with `npm install grunt-cli`)
- `composer`
- `phpunit`

## Coding Style

The Charcoal-App module follows the Charcoal coding-style:

- [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
- [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
- [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
- [_phpDocumentor_](http://phpdoc.org/)
	- Read the [phpcs.xml](phpcs.xml) file for all the details.

> Coding style validation / enforcement can be performed with `grunt phpcs`. An auto-fixer is also available with `grunt phpcbf`.

## Authors

- Mathieu Ducharme <mat@locomotive.ca>

## Changelog

### 0.1

_Unreleased_
- Initial release
