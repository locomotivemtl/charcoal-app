<?php

namespace Charcoal\App\Route;

use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

// From 'charcoal-app'
use Charcoal\App\Action\ActionInterface;
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\ActionRouteConfig;

/**
 * Action Route Handler.
 */
class ActionRoute implements
    RouteInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
     * Create new action route
     *
     * ### Dependencies
     *
     * **Required**
     *
     * - `config` — ActionRouteConfig
     *
     * **Optional**
     *
     * - `logger` — PSR-3 Logger
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        $this->setConfig($data['config']);
    }

    /**
     * ConfigurableTrait > createConfig()
     *
     * @param mixed|null $data Optional config data.
     * @return ActionRouteConfig
     */
    public function createConfig($data = null)
    {
        return new ActionRouteConfig($data);
    }

    /**
     * @param Container         $container A container instance.
     * @param RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $config = $this->config();

        $actionController = $config['controller'];

        $action = $container['action/factory']->create($actionController);
        $action->init($request);

        // Set custom data from config.
        $action->setData($config['action_data']);

        // Set headers if necessary.
        if (!empty($config['headers'])) {
            foreach ($config['headers'] as $name => $val) {
                $response = $response->withHeader($name, $val);
            }
        }

        // Run (invoke) action.
        return $action($request, $response);
    }
}
