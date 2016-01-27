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

use \Pimple\Container;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Template\TemplateInterface;
use \Charcoal\App\Template\TemplateFactory;

// Local namespace dependencies
use \Charcoal\App\Route\RouteInterface;
use \Charcoal\App\Route\TemplateRouteConfig;

/**
 *
 */
class TemplateRoute implements
    AppAwareInterface,
    ConfigurableInterface,
    RouteInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;


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
        $this->setConfig($data['config']);
        $this->setApp($data['app']);
    }

    /**
     * ConfigurableTrait > createConfig()
     *
     * @param mixed|null $data Optional config data.
     * @return ConfigInterface
     */
    public function createConfig($data = null)
    {
        return new TemplateRouteConfig($data);
    }

    /**
     * @param Container         $container A DI (Pimple) container.
     * @param RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     * @todo Implement "view/default_engine" and "view/default_template".
     */
    public function __invoke(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $tplConfig = $this->config();

        // Handle explicit redirects
        if ($tplConfig['redirect'] !== null) {
            return $response->withRedirect(
                $request->getUri()->withPath($tplConfig['redirect']),
                $tplConfig['redirect_mode']
            );
        }

        $templateIdent = $tplConfig['template'];

        if ($tplConfig['cache']) {
            $cachePool = $container['cache'];
            $cacheItem = $cachePool->getItem('template', $templateIdent);

            $templateContent = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $cacheItem->lock();
                $templateContent = $this->templateContent($container);

                $cacheItem->set($templateContent, $tplConfig['cache_ttl']);
            }
        } else {
            $templateContent = $this->templateContent($container);
        }


        $response->write($templateContent);

        return $response;
    }

    /**
     * @param Container $container A DI (Pimple) container.
     * @return string
     */
    protected function templateContent(Container $container)
    {
        $appConfig = $container['config'];
        $tplConfig = $this->config();

        $templateIdent = $tplConfig['template'];
        $templateController = $tplConfig['controller'];

        $fallbackController = $appConfig->get('view/default_controller');

        $templateFactory = $container['template/factory'];

        if ($fallbackController) {
            $templateFactory->setDefaultClass($fallbackController);
        }

        $template = $templateFactory->create($templateController, [
            'app'    => $this->app(),
            'logger' => $container['logger']
        ], function (TemplateInterface $template) use ($container) {
            $template->setDependencies($container);
        });

        $template->setView($container['view']);

        // Set custom data from config.
        $template->setData($tplConfig['template_data']);

        $templateContent = $template->renderTemplate($templateIdent);

        return $templateContent;
    }
}
