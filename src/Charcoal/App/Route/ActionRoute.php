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
use \Charcoal\App\AppInterface;
use \Charcoal\App\Action\ActionFactory;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;
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
     * @var AppInterface $app
     */
    private $app;

    /**
     * Create new action route
     *
     * ### Dependencies
     *
     * **Required**
     *
     * - `config` — ScriptRouteConfig
     * - `app`    — AppInterface
     *
     * **Optional**
     *
     * - `logger` — PSR-3 Logger
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
     * Set the action route's reference to the Charcoal App.
     *
     * @param  AppInterface $app The Charcoal Application instance.
     * @return TemplateRoute Chainable
     */
    protected function set_app(AppInterface $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the action route's reference to the Charcoal App
     *
     * @return AppInterface
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
        $config = $this->config();

        $action_controller = $config['controller'];

        $action_factory = new ActionFactory();
        $action = $action_factory->create($action_controller, [
            'app' => $this->app(),
            'logger' => $this->logger()
        ]);

        $action->set_data($config['action_data']);

        // Run (invoke) action.
        return $action($request, $response);
    }
}
