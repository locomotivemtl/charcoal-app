<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppInterface;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\TemplateRouteConfig;
use \Charcoal\App\Template\TemplateFactory;

/**
 *
 */
class TemplateRoute implements
    ConfigurableInterface,
    LoggerAwareInterface,
    RouteInterface
{
    use ConfigurableTrait;
    use LoggerAwareTrait;

    /**
     * @var AppInterface $app
     */
    private $app;

    /**
     * ## Required dependencies
     * - `config`
     * - `app`
     * - `logger`
     *
     * @param array $data Dependencies (see above).
     */
    public function __construct(array $data)
    {
        $this->set_config($data['config']);
        $this->set_app($data['app']);
        $this->set_logger($data['logger']);
    }

    /**
     * Set the template route's reference to the Charcoal App.
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
     * Get the template route's reference to the Charcoal App
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
        return new TemplateRouteConfig($data);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        // Unused variable
        unset($request);

        $config = $this->config();

        $template_ident = $config['template'];
        $template_controller = $config['controller'];

        $template_factory = new TemplateFactory();
        $template = $template_factory->create($template_controller, [
            'app' => $this->app(),
            'logger' => $this->app()->logger()
        ]);

        $template_view = $template->view();
        $template_view->set_data([
            'template_ident' => $template_ident,
            'engine_type' => $config['engine']
        ]);
        $template->set_view($template_view);

        // Set custom data from config.
        $template->set_data($config['template_data']);


        $template_content = $template->render($template_ident);
        $response->write($template_content);

        return $response;
    }
}
