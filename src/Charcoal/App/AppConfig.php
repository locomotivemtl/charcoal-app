<?php

namespace Charcoal\App;

use Exception;
use InvalidArgumentException;
use UnexpectedValueException;

// From PSR-7
use Psr\Http\Message\UriInterface;

// From Slim
use Slim\Http\Uri;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Charcoal App configuration
 */
class AppConfig extends AbstractConfig
{
    /**
     * The application's timezone.
     *
     * @var string|null
     */
    private $timezone;

    /**
     * The application's name.
     *
     * For internal usage.
     *
     * @var string|null
     */
    private $projectName;

    /**
     * The base URL (public) for the Charcoal installation.
     *
     * @var UriInterface|null
     */
    private $baseUrl;

    /**
     * The base path for the Charcoal installation.
     *
     * @var string|null
     */
    private $basePath;

    /**
     * The path to the public / web directory.
     *
     * @var string|null
     */
    private $publicPath;

    /**
     * The path to the cache directory.
     *
     * @var string|null
     */
    private $cachePath;

    /**
     * The path to the logs directory.
     *
     * @var string|null
     */
    private $logsPath;

    /**
     * Whether the debug mode is enabled (TRUE) or not (FALSE).
     *
     * @var boolean
     */
    private $devMode = false;

    /**
     * The application's routes.
     *
     * @var array
     */
    private $routes = [];

    /**
     * The application's dynamic routes.
     *
     * @var array|boolean
     */
    private $routables = [];

    /**
     * The application's HTTP middleware.
     *
     * @var array
     */
    private $middlewares = [];

    /**
     * The application's handlers.
     *
     * @var array
     */
    private $handlers = [];

    /**
     * The application's modules.
     *
     * @var array
     */
    private $modules = [];

    /**
     * The application's API credentials and service configsets.
     *
     * @var array
     */
    private $apis = [];

    /**
     * The application's caching configset.
     *
     * @var array
     */
    private $cache;

    /**
     * The application's logging configset.
     *
     * @var array
     */
    private $logger;

    /**
     * The application's view/rendering configset.
     *
     * @var array
     */
    protected $view;

    /**
     * The application's database configsets.
     *
     * @var array
     */
    private $databases = [];

    /**
     * The application's default database configset.
     *
     * @var string
     */
    private $defaultDatabase;

    /**
     * The application's filesystem configset.
     *
     * @var array
     */
    private $filesystem;

    /**
     * Default app-config values.
     *
     * @return array
     */
    public function defaults()
    {
        /** @var string $baseDir Presume that Charcoal App _is_ the application */
        $baseDir = rtrim(realpath(__DIR__.'/../../../'), '/').'/';

        return [
            'project_name'     => '',
            'base_path'        => $baseDir,
            'public_path'      => null,
            'timezone'         => 'UTC',
            'routes'           => [],
            'routables'        => [],
            'middlewares'      => [],
            'handlers'         => [],
            'modules'          => [],
            'cache'            => [],
            'logger'           => [],
            'view'             => [],
            'databases'        => [],
            'default_database' => 'default',
            'dev_mode'         => false
        ];
    }

    /**
     * Replaces placeholders (%app.key%) by their values in the config.
     *
     * @param  mixed $value A value to resolve.
     * @throws UnexpectedValueException If the resolved value is not a string or number.
     * @return mixed
     */
    public function resolveValue($value)
    {
        $tags = [
            'app.base_path'   => $this->basePath(),
            'app.public_path' => $this->publicPath(),
            'app.cache_path'  => $this->cachePath(),
            'app.logs_path'   => $this->logsPath(),
        ];

        if (is_string($value)) {
            return preg_replace_callback('/%%|%([^%\s]+)%/', function ($match) use ($tags, $value) {
                // skip escaped %%
                if (!isset($match[1])) {
                    return '%%';
                }

                $tag = $match[1];

                $resolved = ($tags[$tag] ?? null);

                if (!is_string($resolved) && !is_numeric($resolved)) {
                    $resolvedType = (is_object($resolved) ? get_class($resolved) : gettype($resolved));

                    throw new UnexpectedValueException(sprintf(
                        'Invalid config parameter "%s" inside string value "%s"; must be a string or number, received %s',
                        $key,
                        $value,
                        $resolvedType
                    ));
                }

                return $resolved;
            }, $value);
        }

        return $value;
    }

    /**
     * Set the application's absolute root path.
     *
     * Resolves symlinks with realpath() and ensure trailing slash.
     *
     * @param  string $path The absolute path to the application's root directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setBasePath($path)
    {
        if ($path === null) {
            throw new InvalidArgumentException(
                'The base path is required.'
            );
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The base path must be a string'
            );
        }

        $this->basePath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute root path.
     *
     * @return string The absolute path to the application's root directory.
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Set the application's absolute path to the public web directory.
     *
     * @param  string $path The path to the application's public directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setPublicPath($path)
    {
        if ($path === null) {
            $this->publicPath = null;
            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The public path must be a string'
            );
        }

        $this->publicPath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute path to the public web directory.
     *
     * @return string The absolute path to the application's public directory.
     */
    public function publicPath()
    {
        if ($this->publicPath === null) {
            $this->publicPath = $this->basePath().DIRECTORY_SEPARATOR.'www';
        }

        return $this->publicPath;
    }

    /**
     * Set the application's absolute path to the cache directory.
     *
     * @param  string $path The path to the application's cache directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setCachePath($path)
    {
        if ($path === null) {
            $this->cachePath = null;
            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The cache path must be a string'
            );
        }

        $this->cachePath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute path to the cache directory.
     *
     * @return string The absolute path to the application's cache directory.
     */
    public function cachePath()
    {
        if ($this->cachePath === null) {
            $this->cachePath = $this->basePath().DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'cache';
        }

        return $this->cachePath;
    }

    /**
     * Set the application's absolute path to the logs directory.
     *
     * @param  string $path The path to the application's logs directory.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setLogsPath($path)
    {
        if ($path === null) {
            $this->logsPath = null;
            return $this;
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'The logs path must be a string'
            );
        }

        $this->logsPath = rtrim(realpath($path), '\\/');
        return $this;
    }

    /**
     * Retrieve the application's absolute path to the logs directory.
     *
     * @return string The absolute path to the application's logs directory.
     */
    public function logsPath()
    {
        if ($this->logsPath === null) {
            $this->logsPath = $this->basePath().DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'logs';
        }

        return $this->logsPath;
    }

    /**
     * Set the application's fully qualified base URL to the public web directory.
     *
     * @param  UriInterface|string $uri The base URI to the application's web directory.
     * @return self
     */
    public function setBaseUrl($uri)
    {
        if (is_string($uri)) {
            $this->baseUrl = Uri::createFromString($uri);
        } else {
            $this->baseUrl = $uri;
        }
        return $this;
    }

    /**
     * Retrieve the application's fully qualified base URL to the public web directory.
     *
     * @return UriInterface|null The base URI to the application's web directory.
     */
    public function baseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set the application's default timezone.
     *
     * @param  string $timezone The timezone string.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setTimezone($timezone)
    {
        if (!is_string($timezone)) {
            throw new InvalidArgumentException(
                'Timezone must be a string.'
            );
        }

        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Retrieve the application's default timezone.
     *
     * Will be used by the PHP date and date-time functions.
     *
     * @return string
     */
    public function timezone()
    {
        if (isset($this->timezone)) {
            return $this->timezone;
        } else {
            return 'UTC';
        }
    }

    /**
     * Sets the project name.
     *
     * @param string|null $projectName The project name.
     * @throws InvalidArgumentException If the project argument is not a string (or null).
     * @return self
     */
    public function setProjectName($projectName)
    {
        if ($projectName === null) {
            $this->projectName = null;
            return $this;
        }
        if (!is_string($projectName)) {
            throw new InvalidArgumentException(
                'Project name must be a string'
            );
        }

        $this->projectName = $projectName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function projectName()
    {
        if ($this->projectName === null) {
            $baseUrl = $this->baseUrl();
            if ($baseUrl) {
                return $baseUrl->getHost();
            }
        }

        return $this->projectName;
    }

    /**
     * @param boolean $devMode The "dev mode" flag.
     * @return self
     */
    public function setDevMode($devMode)
    {
        $this->devMode = !!$devMode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function devMode()
    {
        return !!$this->devMode;
    }

    /**
     * Configure the application's global view service.
     *
     * @param  array $view The global configset for the application's view service.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setView(array $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Retrieve the configset for application's global view service.
     *
     * @return array
     */
    public function view()
    {
        return $this->view;
    }

    /**
     * Parse the application's API configuration.
     *
     * @param  array $apis The API configuration structure to set.
     * @return self
     */
    public function setApis(array $apis)
    {
        $this->apis = $apis;
        return $this;
    }

    /**
     * @return array
     */
    public function apis()
    {
        return $this->apis;
    }

    /**
     * Parse the application's route configuration.
     *
     * @see    \Charcoal\Admin\Config::setRoutes() For a similar implementation.
     * @param  array $routes The route configuration structure to set.
     * @return self
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return array
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * @param  array|boolean $routables The routable configuration structure to set or FALSE to disable dynamic routing.
     * @return self
     */
    public function setRoutables($routables)
    {
        if ($routables !== false) {
            if (!is_array($routables) || empty($routables)) {
                $routables = [];
            }
        }

        $this->routables = $routables;
        return $this;
    }

    /**
     * @return array|boolean
     */
    public function routables()
    {
        return $this->routables;
    }

    /**
     * Parse the application's HTTP middleware.
     *
     * @param  array $middlewares The middleware configuration structure to set.
     * @return self
     */
    public function setMiddlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * @return array
     */
    public function middlewares()
    {
        return $this->middlewares;
    }

    /**
     * Define custom response and error handlers.
     *
     * Charcoal overrides four of Slim's standard handlers:
     *
     * - "notFoundHandler"
     * - "notAllowedHandler"
     * - "errorHandler"
     * - "phpErrorHandler"
     *
     * @param  array $handlers The handlers configuration structure to set.
     * @return self
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = $handlers;
        return $this;
    }

    /**
     * @return array
     */
    public function handlers()
    {
        return $this->handlers;
    }

    /**
     * Set the configuration modules.
     *
     * @param array $modules The module configuration structure to set.
     * @return self
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * @return array
     */
    public function modules()
    {
        return $this->modules;
    }

    /**
     * Configure the application's global cache service.
     *
     * @param  array $cache The global config for the application's cache service.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setCache(array $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Retrieve the configset for application's global cache service.
     *
     * @return array
     */
    public function cache()
    {
        return $this->cache;
    }

    /**
     * Configure the application's global logger service.
     *
     * @param  array $logger The global config for the application's logger service.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setLogger(array $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Retrieve the configset for application's global logger service.
     *
     * @return array
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * @param array $databases The avaiable databases config.
     * @return self
     */
    public function setDatabases(array $databases)
    {
        $this->databases = $databases;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method and no databases were set.
     * @return array
     */
    public function databases()
    {
        if ($this->databases === null) {
            throw new Exception(
                'Invalid app config: Databases are not set.'
            );
        }
        return $this->databases;
    }

    /**
     * @param string $ident The ident of the database to return the configuration of.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @throws Exception If trying to access an invalid database.
     * @return array
     */
    public function databaseConfig($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Invalid app config: default database must be a string.'
            );
        }
        $databases = $this->databases();
        if (!isset($databases[$ident])) {
            throw new Exception(
                sprintf('Invalid app config: no database configuration matches "%s".', $ident)
            );
        }
        return $databases[$ident];
    }

    /**
     * @param string $defaultDatabase The default database ident.
     * @throws InvalidArgumentException If the argument is not a string.
     * @return self
     */
    public function setDefaultDatabase($defaultDatabase)
    {
        if (!is_string($defaultDatabase)) {
            throw new InvalidArgumentException(
                'Invalid app config: Default database must be a string.'
            );
        }
        $this->defaultDatabase = $defaultDatabase;
        return $this;
    }

    /**
     * @param string $ident  The database ident.
     * @param array  $config The database options.
     * @throws InvalidArgumentException If the arguments are invalid.
     * @return self
     */
    public function addDatabase($ident, array $config)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Invalid app config: database ident must be a string.'
            );
        }

        if ($this->databases === null) {
            $this->databases = [];
        }
        $this->databases[$ident] = $config;
        return $this;
    }

    /**
     * @throws Exception If trying to access this method before a setter.
     * @return mixed
     */
    public function defaultDatabase()
    {
        if ($this->defaultDatabase === null) {
            throw new Exception(
                'Invalid app config: default database is not set.'
            );
        }
        return $this->defaultDatabase;
    }

    /**
     * Configure the application's global file system.
     *
     * @param  array $filesystem The global config for the application's file system.
     * @throws InvalidArgumentException If the argument is not a configset.
     * @return self
     */
    public function setFilesystem(array $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * Retrieve the configset for application's global file system.
     *
     * @return array
     */
    public function filesystem()
    {
        return $this->filesystem;
    }
}
