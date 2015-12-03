<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigInterface;
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\App;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\ScriptRouteConfig;
use \Charcoal\App\Script\ScriptFactory;

/**
 *
 */
class ScriptRoute implements
    ConfigurableInterface,
    LoggerAwareInterface,
    RouteInterface
{
    use ConfigurableTrait;
    use LoggerAwareTrait;

    /**
     * @var App $app
     */
    private $app;

    /**
     * ## Required dependencies
     * - `config` ScriptRouteConfig
     * - `app` App
     * - `logger` PSR-3 logger
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        $this->set_config($data['config']);
        $this->set_app($data['app']);
        $this->set_logger($data['logger']);
    }

    /**
     * Set the script's reference to the Charcoal App.
     *
     * @param  App $app The Charcoal Application instance.
     * @return TemplateRoute Chainable
     */
    protected function set_app(App $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the script's reference to the Charcoal App
     *
     * @return App
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
        return new ScriptRouteConfig($data);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $config = $this->config();

        $script_ident = $config['ident'];

        $script_factory = new ScriptFactory();
        $script = $script_factory->create($script_ident, [
            'app' => $this->app()
        ]);

        $action->set_data($config['script_data']);
        
        return $response;
    }
}
