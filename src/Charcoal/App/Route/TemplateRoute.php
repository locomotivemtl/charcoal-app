<?php

namespace Charcoal\App\Route;

use \InvalidArgumentException;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Local namespace dependencies
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\TemplateRouteConfig;

class TemplateRoute implements
    RouteInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * @var \Slim\App $app
    */
    private $app;

    private $logger;

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

        $this->logger = $this->app->logger;
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
        $container = $this->app->getContainer();
        $app_config = $container['config'];

        $config = $this->config();

        $controller = $config['controller'];
        if ($controller === null) {
            $controller=  $config['ident'];
        }

        $template = $config['template'];
        $engine = $config['engine'];
        $options = $config['options'];


        $this->logger->debug('RESPONDING to '.$config['ident']);
        $this->logger->debug('Engine :'.$engine);
        $this->logger->debug('Template: '.$template);


        $template_loader = new \Charcoal\View\Mustache\MustacheLoader([
            'search_path'=>$app_config['view/path']
        ]);


        $view_engine = new \Charcoal\View\Mustache\MustacheEngine([
            'logger'=>$this->logger,
           // 'cache'=>$container['cache'],
            'loader'=>$template_loader
        ]);
            
        $view = new \Charcoal\View\GenericView([
            'engine' => $view_engine,
            'logger' => $this->logger
        ]);



        $content = $view->render($template, $controller);
        $response->write($content);

        return $response;

    }
}
