<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// From `charcoal-config`
use \Charcoal\Config\ConfigInterface;
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\ScriptRouteConfig;

class ScriptRoute implements RouteInterface, ConfigurableInterface
{
    use ConfigurableTrait;

    private $app;

    /**
    * Dependencies:
    * - `config`
    * - `app`
    */
    public function __construct($data)
    {
        $this->set_config($data['config']);

        $this->app = $data['app'];
        if (!($this->app instanceof \Slim\App)) {
            throw new InvalidArgumentException(
                'App requires a Slim App object in its dependency container.'
            );
        }
    }

    /**
    * ConfigurableTrait > create_config()
    */
    public function create_config($data = null)
    {
        return new ScriptRouteConfig($data);
    }

    /**
    * @return void
    */
    public function __invoke($request, $response)
    {

        return $response;
    }
}
