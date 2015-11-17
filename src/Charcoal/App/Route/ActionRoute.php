<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigInterface;
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\ActionRouteConfig;

class ActionRoute implements
    RouteInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
    * Dependencies:
    * - `config` \Charcoal\Config\ConfigInterface
    * - `app`
    *
    * @throws InvalidArgumentException
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
        return new ActionRouteConfig($data);
    }

    /**
    * @return  void
    */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        return $response;
    }
}
