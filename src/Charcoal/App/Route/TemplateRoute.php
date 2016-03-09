<?php

namespace Charcoal\App\Route;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Pimple\Container;

use \Slim\Http\Uri;

// From `charcoal-config`
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Intra-module (`charcoal-app`) dependencies
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
    ConfigurableInterface,
    RouteInterface
{
    use ConfigurableTrait;

    /**
     * Create new template route
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
     * @param array|\ArrayInterface $data Dependencies.
     */
    public function __construct($data)
    {
        $this->setConfig($data['config']);
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
     */
    public function __invoke(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $config = $this->config();

        // Handle explicit redirects
        if (!empty($config['redirect'])) {
            $uri   = $request->getUri();
            $parts = parse_url($config['redirect']);

            if (!empty($parts)) {
                if (isset($parts['host'])) {
                    $uri = Uri::createFromString($config['redirect']);
                } else {
                    if (isset($parts['path'])) {
                        $uri = $uri->withPath($parts['path']);
                    }

                    if (isset($parts['query'])) {
                        $uri = $uri->withQuery($parts['query']);
                    }

                    if (isset($parts['fragment'])) {
                        $uri = $uri->withFragment($parts['fragment']);
                    }
                }

                if ((string)$uri !== (string)$request->getUri()) {
                    return $response->withRedirect(
                        $uri,
                        $config['redirect_mode']
                    );
                }
            }
        }

        $templateIdent = $config['template'];

        if ($config['cache']) {
            $cachePool = $container['cache'];
            $cacheItem = $cachePool->getItem('template', $templateIdent);

            $templateContent = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $cacheItem->lock();
                $templateContent = $this->templateContent($container);

                $cacheItem->set($templateContent, $config['cache_ttl']);
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
        $config = $this->config();

        $templateIdent      = $config['template'];
        $templateController = $config['controller'];

        $templateFactory = $container['template/factory'];
        $templateFactory->setDefaultClass($config['defaultController']);

        $template = $templateFactory->create(
            $templateController,
            [
                'logger' => $container['logger']
            ],
            function (TemplateInterface $template) use ($container) {
                $template->setDependencies($container);
            }
        );

        $template->setView($container['view']);

        // Set custom data from config.
        $template->setData($config['template_data']);

        $templateContent = $template->render($templateIdent);

        return $templateContent;
    }
}
