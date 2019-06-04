<?php

namespace Charcoal\App\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Uri;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

// From 'charcoal-app'
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\TemplateRouteConfig;

/**
 * Template Route Handler.
 *
 * A route handler is a simple `invokale` object with the signature:
 * `__invoke(Container $container, RequestInterface $request, ResponseInterface $response)`
 * It is only called (invoked) when a route is matched.
 *
 * This is the default "Slim Route Handler" for _template_ style routes.
 * It uses a `TemplateRouteConfig` to properly either:
 *
 * - redirect the request, if explicitely set
 * - load and render a "Template" object
 *
 * Templates can be any objects that can be loaded with a "TemplateFactory".
 * The Template Factory used is an external dependency (`template/factory`) expected to be set on the container.
 *
 * Template-level cache is possible by setting the "cache" config option to true.
 * Cached template can not have any options; they will always return the exact same content for all "template".
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
     * **Required dependencies**
     *
     * - `config` — TemplateRouteConfig
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        $this->setConfig($data['config']);
    }

    /**
     * ConfigurableTrait > createConfig()
     *
     * @param  mixed|null $data Optional config data.
     * @return TemplateRouteConfig
     */
    public function createConfig($data = null)
    {
        return new TemplateRouteConfig($data);
    }

    /**
     * @param  Container         $container A DI (Pimple) container.
     * @param  RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(
        Container $container,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $config = $this->config();

        // Handle explicit redirects
        if (!empty($config['redirect'])) {
            $redirect = $container['translator']->translation($config['redirect']);
            $uri = $this->parseRedirect((string)$redirect, $request);

            if ($uri) {
                return $response
                    ->withHeader('Location', (string)$uri)
                    ->withStatus($config['redirect_mode']);
            }
        }

        $templateContent = $this->templateContent($container, $request);

        $response->getBody()->write($templateContent);

        if (!empty($config['headers'])) {
            foreach ($config['headers'] as $name => $val) {
                $response = $response->withHeader($name, $val);
            }
        }


        return $response;
    }

    /**
     * @param  Container        $container A DI (Pimple) container.
     * @param  RequestInterface $request   The request to intialize the template with.
     * @return string
     */
    protected function templateContent(
        Container $container,
        RequestInterface $request
    ) {
        if ($this->cacheEnabled()) {
            $cachePool = $container['cache'];
            $cacheKey  = 'template/'.str_replace('/', '.', $this->cacheIdent());
            $cacheItem = $cachePool->getItem($cacheKey);

            $template = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $template = $this->renderTemplate($container, $request);

                $cacheItem->set($template, $this->cacheTtl());
                $cachePool->save($cacheItem);
            }
        } else {
            $template = $this->renderTemplate($container, $request);
        }

        return $template;
    }

    /**
     * @param  Container        $container A DI (Pimple) container.
     * @param  RequestInterface $request   The request to intialize the template with.
     * @return string
     */
    protected function renderTemplate(Container $container, RequestInterface $request)
    {
        $config   = $this->config();
        $template = $this->createTemplate($container, $request);

        return $container['view']->render($config['template'], $template);
    }

    /**
     * @param  Container        $container A DI (Pimple) container.
     * @param  RequestInterface $request   The request to intialize the template with.
     * @return string
     */
    protected function createTemplate(Container $container, RequestInterface $request)
    {
        $config = $this->config();

        $templateFactory = $container['template/factory'];
        if ($config['default_controller'] !== null) {
            $templateFactory->setDefaultClass($config['default_controller']);
        }

        $template = $templateFactory->create($config['controller']);
        $template->init($request);

        // Set custom data from config.
        $template->setData($config['template_data']);

        return $template;
    }

    /**
     * @param  string           $redirection The route's destination.
     * @param  RequestInterface $request     A PSR-7 compatible Request instance.
     * @return Uri|null
     */
    protected function parseRedirect($redirection, RequestInterface $request)
    {
        $uri   = $request->getUri()->withUserInfo('');
        $parts = parse_url($redirection);

        if (!empty($parts)) {
            if (isset($parts['host'])) {
                $uri = Uri::createFromString($redirection);
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
                return $uri;
            }
        }

        return null;
    }

    /**
     * Determine if the cache is enabled.
     *
     * @return boolean
     */
    protected function cacheEnabled()
    {
        return $this->config('cache');
    }

    /**
     * Retrieve the time-to-live value for the cache.
     *
     * @return integer
     */
    protected function cacheTtl()
    {
        return $this->config('cache_ttl');
    }

    /**
     * Retrieve the cache key.
     *
     * @return string
     */
    protected function cacheIdent()
    {
        return $this->config('template');
    }
}
