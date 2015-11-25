<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\Template\TemplateFactory;

// Local namespace dependencies
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\TemplateRouteConfig;

class TemplateRoute implements
    ConfigurableInterface,
    LoggerAwareInterface,
    RouteInterface
{
    use ConfigurableTrait;

    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
     * @var LoggerInterface $logger
    */
    private $logger;

    /**
    * Dependencies:
    * - `config`
    * - `app`
    *
    * @param array $data Dependencies
    * @throws InvalidArgumentException
    */
    public function __construct(array $data)
    {
        $this->set_config($data['config']);

        $this->app = $data['app'];
        if (!($this->app instanceof \Slim\App)) {
            throw new InvalidArgumentException(
                'App requires a Slim App object in its dependency container.'
            );
        }

        // Reuse app logger, if it's not directly set in data dependencies
        $logger = isset($data['logger']) ? $data['logger'] : $this->app->logger;
        $this->set_logger($logger);
    }

    /**
    * > LoggerAwareInterface > setLogger()
    *
    * Fulfills the PSR-1 style LoggerAwareInterface
    *
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function setLogger(LoggerInterface $logger)
    {
        return $this->set_logger($logger);
    }

    /**
    * @param LoggerInterface $logger
    * @return AbstractEngine Chainable
    */
    public function set_logger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
    * @erturn LoggerInterface
    */
    public function logger()
    {
        return $this->logger;
    }

    /**
    * ConfigurableTrait > create_config()
    */
    public function create_config($data = null)
    {
        return new TemplateRouteConfig($data);
    }

    /**
    * @return void
    */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $config = $this->config();

        $template_ident = $config['template'] ?: $config['ident'];

        $template = TemplateFactory::instance()->create($template_ident, [
            'app' => $this->app
        ]);

        $response->write($template->render($template_ident));

        return $response;
    }
}
