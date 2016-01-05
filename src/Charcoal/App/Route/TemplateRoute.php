<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\TemplateRouteConfig;
use \Charcoal\App\Template\TemplateFactory;

/**
 *
 */
class TemplateRoute implements
    AppAwareInterface,
    ConfigurableInterface,
    LoggerAwareInterface,
    RouteInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;
    use LoggerAwareTrait;


    /**
     * Create new template route
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
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        $this->set_config($data['config']);
        $this->set_app($data['app']);
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
     * @todo Implement "view/default_engine" and "view/default_template".
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $tpl_config = $this->config();
        $app_config = $this->app()->config();

        // Handle explicit redirects
        if ($tpl_config['redirect'] !== null) {
            return $response->withRedirect(
                $request->getUri()->withPath($tpl_config['redirect']),
                $tpl_config['redirect_mode']
            );
        }

        $template_ident = $tpl_config['template'];
        $template_controller = $tpl_config['controller'];

        $fallback_controller = $app_config->get('view/default_controller');

        $template_factory = new TemplateFactory();

        if ($fallback_controller) {
            $template_factory->set_default_class($fallback_controller);
        }

        $template = $template_factory->create($template_controller, [
            'app'    => $this->app(),
            'logger' => $this->logger
        ]);

        $template_view = $template->view();
        $template_view->set_data([
            'template_ident' => $template_ident,
            'engine_type'    => $tpl_config['engine']
        ]);

        $template->set_view($template_view);

        // Set custom data from config.
        $template->set_data($tpl_config['template_data']);

        $template_content = $template->render_template($template_ident);

        $response->write($template_content);

        return $response;
    }
}
