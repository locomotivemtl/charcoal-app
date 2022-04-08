<?php

namespace Charcoal\App\Handler;

use RuntimeException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\Translator;

// From 'charcoal-view'
use Charcoal\View\ViewInterface;

/**
 * Template rendering for handlers
 */
trait TemplateableHandlerTrait
{
    /**
     * Render the body of the HTML error.
     *
     * @return string
     */
    public function renderHtmlContent()
    {
        return $this->view()->render($this->templatePartial(), $this->getTemplateData());
    }

    /**
     * Render HTML handler.
     *
     * @see    \Charcoal\App\Route\TemplateRoute::templateContent() Equivalent
     * @return string
     */
    protected function renderHtmlTemplate()
    {
        if ($this->cacheEnabled()) {
            $cachePool = $this->container['cache'];
            $cacheKey  = 'template/'.str_replace('/', '.', $this->cacheIdent());
            $cacheItem = $cachePool->getItem($cacheKey);

            $output = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $output = $this->renderHtmlTemplateContent();

                $cacheItem->set($output, $this->cacheTtl());
                $cachePool->save($cacheItem);
            }
        } else {
            $output = $this->renderHtmlTemplateContent();
        }

        return $output;
    }

    /**
     * Render HTML template.
     *
     * @see    \Charcoal\App\Route\TemplateRoute::renderTemplate() Equivalent
     * @see    \Charcoal\App\Route\TemplateRoute::createTemplate() Equivalent
     * @return string
     */
    protected function renderHtmlTemplateContent()
    {
        $config = $this->config();

        $factory = $this->templateFactory();
        $factory->setDefaultClass($config['default_controller']);

        $controller = $factory->create($config['controller']);
        $controller->init($this->httpRequest());
        $controller['app_handler'] = $this;
        $controller->setData($this->getTemplateData());

        foreach ($config['partials'] as $varName => $templateIdent) {
            $this->view()->setDynamicTemplate($varName, $templateIdent);
        }

        return $this->view()->render($config['template'], $controller);
    }

    /**
     * Prepare the template data for rendering.
     *
     * @param  array|\ArrayAccess $data Raw template data.
     * @return array|\ArrayAccess Expanded and processed template data.
     */
    protected function parseTemplateData($data = [])
    {
        return $data;
    }

    /**
     * Retrieve the final handler data for rendering the view.
     *
     * @return array Finalized template data.
     */
    final protected function getTemplateData()
    {
        $config = $this->config();

        return $this->parseTemplateData($config['template_data']);
    }

    /**
     * Retrieve the final handler data for rendering the view.
     *
     * @throws RuntimeException If the "handlerMessage" view is missing.
     * @return void
     */
    protected function resolveTemplatePartial()
    {
        $config = $this->config();
        if (empty($config['partial'])) {
            $config['partial'] = static::DEFAULT_PARTIAL;
        }
    }

    /**
     * Retrieve the final handler data for rendering the view.
     *
     * @return array Finalized template data.
     */
    final protected function templatePartial()
    {
        return $this->config('partial');
    }

    /**
     * Determine if the cache is enabled.
     *
     * @see    \Charcoal\App\Route\TemplateRoute::cacheEnabled() Equivalent
     * @return boolean
     */
    final protected function cacheEnabled()
    {
        return $this->config('cache');
    }

    /**
     * Retrieve the time-to-live value for the cache.
     *
     * @see    \Charcoal\App\Route\TemplateRoute::cacheTtl() Equivalent
     * @return integer
     */
    final protected function cacheTtl()
    {
        return $this->config('cache_ttl');
    }

    /**
     * Retrieve the cache key.
     *
     * @see    \Charcoal\App\Route\TemplateRoute::cacheIdent() Equivalent
     * @return string
     */
    final protected function cacheIdent()
    {
        return $this->config('template');
    }

    /**
     * Retrieve the template factory.
     *
     * @return FactoryInterface
     */
    abstract protected function templateFactory();

    /**
     * Retrieve the HTTP request.
     *
     * @return ServerRequestInterface
     */
    abstract protected function httpRequest();

    /**
     * Retrieve the renderable view.
     *
     * @return ViewInterface
     */
    abstract public function view();
}
