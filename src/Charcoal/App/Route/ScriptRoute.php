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
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\ScriptRouteConfig;

/**
 * Script Route Handler.
 */
class ScriptRoute implements
    ConfigurableInterface,
    RouteInterface
{
    use ConfigurableTrait;

    /**
     * Create new script route (CLI)
     *
     * ### Dependencies
     *
     * **Required**
     *
     * - `config` — ScriptRouteConfig
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
     * ConfigurableTrait > create_config()
     *
     * @param mixed|null $data Optional config data.
     * @return ScriptRouteConfig
     */
    public function createConfig($data = null)
    {
        return new ScriptRouteConfig($data);
    }

    /**
     * @param Container         $container A dependencies container.
     * @param RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $config = $this->config();

        $scriptController = $config['controller'];

        $script = $container['script/factory']->create($scriptController);

        $scriptData = $config['scriptData'];
        if (empty($scriptData) === false) {
            $script->setData($scriptData);
        }

        // Run (invoke) script.
        return $script($request, $response);
    }
}
