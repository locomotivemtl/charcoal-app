<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\TemplateRouteConfig;

class TemplateRoute implements
    LoggerAwareInterface,
    RouteInterface,
    ConfigurableInterface
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
    public function __invoke($request, $response)
    {
        unset($request);

        $container = $this->app->getContainer();
        $app_config = $container['charcoal/app/config'];

        $config = $this->config();

        $controller = $config['controller'];
        if ($controller === null) {
            $controller = $config['ident'];
        }
        $template = $config['template'];
        $engine = $config['engine'];
        $options = $config['options'];

        $this->logger()->debug('RESPONDING to '.$config['ident']);
        $this->logger()->debug('Engine :'.$engine);
        $this->logger()->debug('Template: '.$template);
        $this->logger()->debug('Controller: '.$controller);

        $template_loader = new \Charcoal\View\Mustache\MustacheLoader([
            'search_path' => $app_config['view/path']
        ]);

        $view_engine = new \Charcoal\View\Mustache\MustacheEngine([
            'logger' => $this->logger(),
           // 'cache' => $container['cache'],
            'loader' => $template_loader
        ]);
            
        $view = new \Charcoal\View\GenericView([
            'engine' => $view_engine,
            'logger' => $this->logger()
        ]);

        $content = $view->render($template, $controller);
        $response->write($content);

        return $response;
    }
}
