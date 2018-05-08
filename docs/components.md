# Components

The main components of charcoal-app are:

-   [Config](#config-component)
-   [App](#app-compoment)
-   ~~[Module](#module-component)~~
-   [Routes & Request Controllers](#routes--request-controllers)
    -   [Action](#action-request-controller)
    -   [Script](#script-request-controller)
    -   [Template](#template-request-controller)
    -   [Route API](#route-api)
-   [Routable objects](#routable-objects)
-   [Charcoal Binary](#charcoal-binary)
-   [PHPUnit Tests](#phpunit-tests)



## Config Component

At the core of a _Charcoal application_ is a highy customizable configuration store: `Charcoal\App\AppConfig`, provided by [`locomotivemtl/charcoal-config`][charcoal-config].

Typically, the application's configuration should load a file located in `config/config.php`. This file might load other, specialized, config files (PHP, JSON, or INI).

In the front controller, ensure the configuration is loaded:

```php
$config = new \Charcoal\App\AppConfig();
$config->addFile(__DIR__.'/../config/config.php');
```

> It is recommended to keep a separate _config_ file for all of your different app modules. 
> Compartmentalized config sections are easier to maintain and understand.
>
> The [official boilerplate][gh-charcoal-boilerplate] provides a good example of a configuration setup.

### Base App Configuration

| Key                  | Type      | Default | Description |
| -------------------- | --------- | ------- | ----------- |
| **base_path**        | `array`   | `[]`    |             |
| **base_url**         | `array`   | `[]`    |             |
| **ROOT**             | `array`   | `[]`    | An alias of `base_path`.
| **timezone**         | `string`  | `"UTC"` | The current timezone.

### Module & App Configuration

`\Charcoal\App\AppConfig` API:

| Key                  | Type      | Default | Description |
| -------------------- | --------- | ------- | ----------- |
| **modules**          | `array`   | `[]`    |             |
| **routables**        | `array`   | `[]`    |             |
| **routes**           | `array`   | `[]`    |             |
| **service_providers**| `array`   | `[]`    |             |

> The main app can be seen, in a way, as the "default module".

### Basic Service Configuration

| Key                  | Type      | Default | Description |
| -------------------- | --------- | ------- | ----------- |
| **cache**            | `array`   | `null`  |             |
| **databases**        | `array`   | `[]`    | An array of `DatabaseConfig`
| **default_database** | `string`  | `""`    |             |
| **email**            | `array`   | `[]`    | The email (default from and SMTP options) configuration. See [`EmailConfig`][gh-charcoal-email]
| **filesystem**       | `array`   | `null`  |             |
| **logger**           | `array`   | `null`  | The logger service configuration
| **translator**       | `array`   | `null`  |             |
| **view**             | `array`   | `null`  | The default view configuration (default engine and path settings). See [`ViewConfig`][gh-charcoal-view].

### Examples

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
    "service_providers": [
        "foo/bar/service-provider/test"
    ],
    "middlewares": {}
}
```



## App Component

The App component is based on [Slim][slim]. 
It actually extends the `\Slim\App` class.

> **What is Slim?**
>
> At its core, Slim is a dispatcher that receives an HTTP request, invokes an appropriate callback routine, and returns an HTTP response.

The **App** is responsible for loading the _modules_, setting up the _routes_ and the _default handlers_ and adding _Service Providers_ to provide external services to the _DI Container_.

Initialize the app in the _Front Controller_:

```php
// Create container and configure it (with charcoal-config)
$container = new \Charcoal\App\AppContainer([
    // Slim Configuration
    'settings' => [
        'displayErrorDetails' => true
    ],
    // Charcoal Configuration; see "Config Component", above.
    'config' => $config
]);

// Charcoal / Slim is the main app
$app = \Charcoal\App\App::instance($container);
$app->run();
```

> The [boilerplate][gh-charcoal-boilerplate] provides a good example of a front controller.



## Module Component

—N/A—



## Routes & Request Controllers

All routes are actually handled by the *Slim* app. Charcoal Routes are just *definition* of a route:

-   An identifier, which typically matches the controller.
-   A RouteConfig structure, which contains:
    -   The `type` of  `RequestController`. This can be:
        -   `Action`
        -   `Script` (_Scripts_ can only be ran from the CLI.)
        -   `Template`
    -   The `route_controller` ident, which will identify the proper controller to create.
        -   Controllers are created from a _resolver_ factory. Their identifier may look like `foo/bar/controller-name`.

Routes can also be (and most likely are in standard web scenario) defined by objects. For example: sections, news, events, etc. 
See `charcoal-object` for the definition of routable objects, and `charcoal-cms` for examples of routable objects.

### Action Request Controller

The default `charcoal-app` action route handler is `charcoal/app/route/action` (`\Charcoal\App\Route\ActionRoute`).

Actions are set on `POST` requests by default, but this can be overridden by setting the `methods` route option.

By default, what this route handler does is instanciate an _Action_ object (the type of object is set with the `controller`, or `ident` option) and invoke it. The _Action_ must implement `\Charcoal\App\Action\ActionInterface`.

#### Actions API

Actions are basic _Charcoal Entities_ (they extend the `\Charcoal\Config\AbstractEntity` class). Actions are meant to be subclassed in custom projects. But it provides the following default options:

| Key             | Type      | Default        | Description |
| --------------- | --------- | -------------- | ----------- |
| **mode**        | `string`  | ``json'`       | The mode can be "json" or "redirect". `json` returns json data; redirect sends a 30X redirect.
| **success**     | `boolean` | `false`        | Wether the action was successful or not. Typically changed in the `run` method.
| **success_url** | `string`  | `null`         |             |
| **failure_url** | `string`  | `null`         |             |

When writing an action, there are only 2 abstract methods that must be added to the _Action_ class:

-   `run(RequestInterface $request, ResponseInterface $response);`
-   `results();`

The run method is ran automatically when _invoking_ an action.

#### Custom Actions

There are 2 steps to creating a custom action for a charcoal-app.

1. Set up the action route
2. Write the Action controller

In the config file (typically, `config/routes.json` loaded from `config/config.php`:

```json
{
    "routes": {
        "actions": {
            "test": {
                "controller": "foo/bar/action/test"
            }
        }
    }
}
```

The controller FQN should match its identifier. In this example, the _Action Factory_ will attempt to load the `foo/bar/action/test` controller, which will match the `\Foo\Bar\Action\TestAction` class. Using _PSR-4_, this class should be located in the source at `src/Foo/Bar/Action/TestAction.php`

```php
namespace Foo\Bar\Action;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Charcoal\App\Action\AbstractAction;

class TestAction extends AbstractAction
{
    private $greetings;

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $this->greetings = 'Hello world!';
        $this->setSuccess(true);
        return $response;
    }

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'   => $this->success(),
            'greetings' => $this->greetings
        ];
    }
}
```

When requesting `http://$URL/test` (with **POST**), the following should be returned:

```json
{
    "success": 1,
    "greetings": "Hello World!"
}
```

### Script Request Controller

The default `charcoal-app` script route handler is `charcoal/app/route/script` (`\Charcoal\App\Route\ScriptRoute`).

Scripts mock a _Slim_ HTTP environment for the _CLI_. Allowing to be route like regular web routes but for a script environment. `charcoal-app` comes with the `charcoal` binary which is meant to run those kind of scripts.

> Thanks to _composer_, the charcoal binary is installed automatically in your project and callable with `php vendor/bin/charcoal`.

By default, what this route handler does is instanciate a _Script_ object (the type of object is set with `controller` or `ident` option) and invoke it. The _Script_ must implement `\Charcoal\App\Script\ScriptInterface`.

> The CLI helper (arguments parser, input and output handlers) is provided by [CLImate][climate].

#### Script API

| Key           | Type     | Default                    | Description |
| ------------- | -------- | -------------------------- | ----------- |
| **arguments** | `array`  | _help, quiet, and verbose_ | The script arguments.

#### Custom Scripts

Creating custom scripts is exactly like creating custom actions:

1. Set up the script route.
2. Write the Script controller.

In the config file (typically, `config/routes.json` loaded from `config/config.php`:

```json
{
    "routes": {
        "scripts": {
            "test": {
                "controller": "foo/bar/script/test"
            }
        }
    }
}
```

The controller FQN should match its identifier. In this example, the _Script Factory_ will attempt to load the `foo/bar/sript/test` controller, which will match the `\Foo\Bar\Script\TestScript` class. Using _PSR-4_, this class should be located in the source at `src/Foo/Bar/Script/TestScript.php`

```php
namespace Foo\Bar\Script;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Charcoal\App\Script\AbstractScript;

class TestScript extends AbstractScript
{
    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $this->climate()->out('Hello World!');
        return $response;
    }

}
```

Calling the script with `./vendor/bin/charcoal test` should output:

```
★ Hello World!
```

### Template Request Controller

The default `charcoal-app` template route handler is `charcoal/app/route/template` (`\Charcoal\App\Route\TemplateRoute`).

Templates are set on `GET` requests by default, but this can be overridden by setting the `methods` route option.

> In a typical charcoal-app project, most "web pages" are served as a Template.

By default, what this route handler does is instanciate a _Template_ object (the type of object is set with the `controller`, or `ident` option) and "render" it. The _Action_ must implement `\Charcoal\App\Action\ActionInterface`.

To render the template, it is important that a `view` has been set properly on the _DI container_. This can be done easily with the [View Service Provider](docs/providers.md#view-service-provider)

#### Custom Templates

Creating custom templates is probably the most common thing to do for a `charcoal-app` project. There are 3 steps involved:

1. Set up the template route
2. Write the template controller
3. Write the template view

> Although it is possible to use different rendering engines, the following example assume the default `mustache` engine.

In the config file (typically, `config/routes.json` loaded from `config/config.php`:

```json
{
    "routes": {
        "templates": {
            "test": {
                "controller": "foo/bar/template/test",
                "template": "foo/bar/template/test"
            }
        }
    }
}
```

The controller FQN should match its identifier. In this example, the _Template Factory_ will attempt to load the `foo/bar/template/test` controller, which will match the `\Foo\Bar\Template\TestTemplate` class. Using _PSR-4_, this class should be located in the source at `src/Foo/Bar/Template/TestTemplate.php`

```php
namespace Foo\Bar\Template;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Charcoal\App\Action\AbstractTemplate;

class TestTemplate extends AbstractTemplate
{
    /**
     * @return string
     */
    public function greetings()
    {
        return 'Hello World!';
    }
}
```

Finally, the _template view_ must also be created. The route config above specified the **template** as `foo/bar/template/test`. Because the default engine (mustache) is used, the loaded file should be located at `templates/foo/bar/template/test.mustache`:

```html
{{> foo/bar/template/inc.header }}

<section class="main">
    {{ greetings }}
</section>

{{> foo/bar/template/inc.footer }}
```

### Route Options

> 👉 Slim's routing is actually provided by [FastRoute][fastroute]

**Common route configuration**

| Key             | Type       | Default     | Description |
| --------------- | ---------- | ----------- | ----------- |
| **ident**       | `string`   | `null`      | Route identifier.
| **route**       | `string`   | `null`      | Route pattern.
| **methods**     | `string[]` | `[ 'GET' ]` | The HTTP methods to wthich this route resolve to. Ex: `['GET', 'POST', 'PUT', 'DELETE']`
| **controller**  | `string`   | `null`      | Controller identifier. Will be guessed from the _ident_ when `null`.
| **lang**        | `string`   | `null`      | The current language.
| **groups**      | `string[]` | `null`      | The route group, if any.

> Additionnaly, a **route_controller** option can be set, to load a custom route handler.

**Action specific configuration**

| Key               | Type     | Default      | Description |
| ----------------- | -------- | ------------ | ----------- |
| **action_data**   | `array`  | `[]`         | Extra / custom action data.

**Script specific configuration**

| Key               | Type     | Default      | Description |
| ----------------- | -------- | ------------ | ----------- |
| **script_data**   | `array`  | `[]`         | Extra / custom script data.

**Template specific configuration**

| Key               | Type      | Default      | Description |
| ----------------- | --------- | ------------ | ----------- |
| **template**      | `string`  | `null`       | The template _ident_ to display.
| **engine**        | `string`  | `'mustache'` | The template _engine_ type. Default Charcoal view engines are `mustache`, `php` and `php-mustache`.
| **template_data** | `array`   | `[]`         | Extra / custom template data.
| **cache**         | `boolean` | `false`      | Set to true to enable template-level cache on this object. This is not recommended for any page that must serve dynamic content.
| **cache_ttl**     | `integer` | `0`          | The _time-to-live_, in seconds, of the cache object, if applicable.

#### Defining routes, in JSON

Here is an example of route definitions. Some things to note:

-   To set the "default" template (GET) route, simply map a route to "/".
-   Most configuration options are optional.
-   The "full" routes in the example below tries to display all posible config options.
    -   Custom route controller
    -   A lot of those are unnecessary, as they are set by default.
    -   The "redirect" option is not set, as it conflicts most other options or renders them unncessary.
-   The same definition could be pure PHP.


```json
{
    "routes": {
        "templates": {
            "/": {
                "redirect": "home"
            },
            "home": {
                "controller": "acme/template/home",
                "template": "acme/template/home"
            },
            "full": {
                "route": "/full",
                "route_controller": "acme/route/template",
                "ident": "full-example",
                "template": "acme/route/full",
                "controller": "acme/route/full",
                "engine": "mustache",
                "methods": ["GET"],
                "cache": false,
                "cache_ttl": 0,
                "template_data": {
                    "custom_options": 42
                }
        }
        },
        "actions": {
            "publish": {
                "controller": "acme/action/blog/publish",
            }
        },
        "scripts": {
            "foo": {
                "controller": "acme/script/foo"
            }
        }
    }
}
```

## Routable Objects

Routes are great to match URL path to template controller or action controller, but needs to **all** be defined in the (main) `AppConfig` configuration.

Routables, on the other hand, are dynamic objects (typically, Charcoal Model objects that implements the `Charcoal\App\Routable\RoutableInterface`) whose _route path_ is typically defined from a dynamic property (and stored in a database).

### The routable callback

The `RoutableInterface` / `RoutableTrait` classes have one abstract method: `handleRoute($path, $request, $response)` which must be implemented in the routable class.

This method should:

-   Check the path to know if it should respond
    -   Typically, this means checking the _path_ parameter against the database to load a matching object.
    -   But really, it could be anything...
-   Return a `callable` object that will handle the route if it matches
-   Return `null` if no match

The returned callable signature should be:
`function(RequestInterface $request, ResponseInterface $response)` and returns a `ResponseInterface`

Routables are called last (only if no explicit routes match fisrt). If no routables return a callable, then a 404 will be sent. (Slim's `NotFoundHandler`).

> The [`charcoal-cms`][charcoal-cms] module contains many good examples of _routable_ objects.

## Middlewares

Just like routes (or everything else "Charcoal", really...), _middlewares_ are set up through the app's _config_.

To be enabled, middlewares must be "active" and they must be accessible from the app's `container`.

For example

There are 2 middlewares provided by default in the `app` module:

- `\Charcoal\App\Middleware\CacheMiddleware`
- `\Charcoal\App\Middleware\Cache\IpMiddleware`

Other Charcoal modules may provide more middlewares (for example, language detection in `charcoal-translator`).

## Charcoal Binary

As previously mentionned, `Script` routes are only available to run from the CLI. A script loader is provided in `bin/charcoal`. It will be installed, with composer, in `vendor/bin/charcoal`.

To view available commands:

```shell
★ ./vendor/bin/charcoal
```

## PHPUnit Tests

Also provided in this package is PSR-7 integration tests helpers, for `phpunit` testing.

The [`\Charcoal\Test\App\ServerTestTrait`](tests/Charcoal/App/ServerTestTrait.php) can be used by any *TestCase* to quickly start the built-in PHP server, performs request and run tests on the result.

```php
use PHPUnit\Framework\TestCase;
use Charcoal\Test\App\ServerTestTrait;

class ExampleTest extends TestCase
{
    use ServerTestTrait;

    public static function setUpBeforeClass()
    {
        static::$serverRoot =  dirname(__DIR__).DIRECTORY_SEPARATOR.'www';
    }

    public function testHomeURLis200()
    {
        $response = $this->callRequest([
            'method'  => 'GET',
            'route'   => '/en/home',
            'options' => null
        ]);
        $this->assertResponseHasStatusCode(200, $response);
    }
}
```

Available methods are:

-   `callRequest(array $request)` to get a ResponseInterface object.
-   `assertResponseMatchesExpected(array $expected, ResponseInterface $response)`
-   `assertResponseHasStatusCode($expectedStatusCode, ResponseInterface $response)`
-   `assertResponseBodyMatchesJson($json, ResponseInterface $response)`
-   `assertResponseBodyRegExp($pattern, ResponseInterface $response)`



[charcoal-admin]:        https://packagist.org/packages/locomotivemtl/charcoal-admin
[charcoal-app]:          https://packagist.org/packages/locomotivemtl/charcoal-app
[charcoal-cache]:        https://packagist.org/packages/locomotivemtl/charcoal-cache
[charcoal-cms]:          https://packagist.org/packages/locomotivemtl/charcoal-cms
[charcoal-config]:       https://packagist.org/packages/locomotivemtl/charcoal-config
[charcoal-translator]:   https://packagist.org/packages/locomotivemtl/charcoal-translator
[charcoal-view]:         https://packagist.org/packages/locomotivemtl/charcoal-view

[gh-charcoal-boilerplate]:  https://github.com/locomotivemtl/charcoal-project-boilerplate
[gh-charcoal-email]:        https://github.com/locomotivemtl/charcoal-email
[gh-charcoal-view]:         https://github.com/locomotivemtl/charcoal-view

[climate]:               https://packagist.org/packages/league/climate
[fastroute]:             https://packagist.org/packages/nikic/fast-route
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
