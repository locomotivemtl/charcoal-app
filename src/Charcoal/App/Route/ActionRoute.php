<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// slim/slim dependencies
use \Slim\App as SlimApp;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigInterface;
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;

// Local namespace dependencies
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\ActionRouteConfig;

/**
 *
 */
class ActionRoute implements
    RouteInterface,
    LoggerAwareInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;
    use LoggerAwareTrait;

    /**
     * @var SlimApp $app
     */
    private $app;

    /**
     * ## Required dependencies
     * - `config`
     * - `app`
     *
     * ## Optional dependencies
     * - `logger`
     *
     * @param array $data Dependencies (see above).
     */
    public function __construct(array $data)
    {
        $this->set_config($data['config']);

        $this->set_app($data['app']);

        // Reuse app logger, if it's not directly set in data dependencies
        $logger = isset($data['logger']) ? $data['logger'] : $this->app->logger;
        $this->set_logger($logger);
    }

    /**
     * Set the action route's reference to the Slim App.
     *
     * @param  SlimApp $app The Slim Application instance.
     * @return TemplateRoute Chainable
     */
    protected function set_app(SlimApp $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the action route's reference to the Slim App
     *
     * @return SlimApp
     */
    protected function app()
    {
        return $this->app;
    }

    /**
     * ConfigurableTrait > create_config()
     *
     * @param mixed|null $data Optional config data.
     * @return ConfigInterface
     */
    public function create_config($data = null)
    {
        return new ActionRouteConfig($data);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        return $response;
    }
}
