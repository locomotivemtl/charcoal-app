<?php

namespace Charcoal\App\Handler;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From PSR-7
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

// From PSR-11
use Psr\Container\ContainerInterface;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;

// From 'charcoal-view'
use Charcoal\View\ViewInterface;
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\Template\TemplateInterface;
use Charcoal\App\Handler\HandlerInterface;
use Charcoal\App\Handler\HandlerConfig;

/**
 * Base Error Handler
 *
 * Enhanced version Slim's error handlers.
 *
 * It outputs messages in either JSON, XML or HTML
 * based on the Accept header.
 */
abstract class AbstractHandler implements
    ConfigurableInterface,
    HandlerInterface,
    LoggerAwareInterface,
    ViewableInterface
{
    use ConfigurableTrait;
    use LoggerAwareTrait;
    use TranslatorAwareTrait;
    use ViewableTrait;

    /**
     * Container
     *
     * @var Container
     */
    protected $container;

    /**
     * URL for the home page
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Known handled content types
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
    ];

    /**
     * Return a new AbstractHandler object.
     *
     * @param ContainerInterface $container A container instance.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setDependencies($container);
    }

    /**
     * Initialize the AbstractHandler object.
     *
     * @return AbstractHandler Chainable
     */
    public function init()
    {
        return $this;
    }

    /**
     * Set dependencies from the service locator.
     *
     * ## Dependencies
     *
     * - `AppConfig $appConfig` — The application's configuration.
     * - `UriInterface $baseUri` — A base URI.
     * - `ViewInterface $view` — A view instance.
     *
     * @param  ContainerInterface $container A service locator.
     * @return AbstractHandler Chainable
     */
    public function setDependencies(ContainerInterface $container)
    {
        $this->setLogger($container['logger']);
        $this->setContainer($container);
        $this->setBaseUrl($container['base-url']);
        $this->setView($container['view']);
        $this->setTranslator($container['translator']);

        if (isset($container['config']['handlers.defaults'])) {
            $this->setConfig($container['config']['handlers.defaults']);
        }

        return $this;
    }

    /**
     * Set container for use with the template controller
     *
     * @param  ContainerInterface $container A container instance.
     * @return AbstractHandler Chainable
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * ConfigurableTrait > createConfig()
     *
     * @see    ConfigurableTrait::createConfig()
     * @param  mixed|null $data Optional config data.
     * @return ConfigInterface
     */
    public function createConfig($data = null)
    {
        return new HandlerConfig($data);
    }

    /**
     * Determine which content type we know about is wanted using "Accept" header
     *
     * @param  ServerRequestInterface $request The most recent Request object.
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);

        if (count($selectedContentTypes)) {
            return reset($selectedContentTypes);
        }

        // handle +json and +xml specially
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/'.$matches[1];
            if (in_array($mediaType, $this->knownContentTypes)) {
                return $mediaType;
            }
        }

        return 'text/html';
    }

    /**
     * Render HTML Error Page
     *
     * @return string
     */
    protected function renderHtmlOutput()
    {
        $config    = $this->config();
        $container = $this->container;

        $templateIdent = $config['template'];

        if ($config['cache']) {
            $cachePool = $container['cache'];
            $cacheKey  = str_replace('/', '.', 'template/'.$templateIdent);
            $cacheItem = $cachePool->getItem($cacheKey);

            $output = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $output = $this->renderHtmlTemplate();

                $cacheItem->set($output, $config['cache_ttl']);
            }
        } else {
            $output = $this->renderHtmlTemplate();
        }

        return $output;
    }

    /**
     * Render title of error
     *
     * @return string
     */
    public function messageTitle()
    {
        return $this->translator()->translate('Application Error');
    }

    /**
     * Render body of HTML error
     *
     * @return string
     */
    abstract public function renderHtmlMessage();

    /**
     * Render HTML Error Page
     *
     * @return string
     */
    protected function renderHtmlTemplate()
    {
        $config    = $this->config();
        $container = $this->container;

        $templateIdent      = $config['template'];
        $templateController = $config['controller'];
        $templateData       = $config['template_data'];

        $templateFactory = $container['template/factory'];
        if (isset($config['default_controller'])) {
            $templateFactory->setDefaultClass($config['default_controller']);
        }

        if (!$templateController) {
            return '';
        }

        $template = $templateFactory->create($templateController);

        if (!isset($templateData['error_title'])) {
            $templateData['error_title'] = $this->messageTitle();
        }

        if (!isset($templateData['error_message'])) {
            $templateData['error_message'] = $this->renderHtmlMessage();
        }

        $template->setData($templateData);
        return $container['view']->render($templateIdent, $template);
    }

    /**
     * Set the base URL (home page).
     *
     * @param  string|UriInterface $url A URL to the base URL.
     * @return AbstractHandler Chainable
     */
    public function setBaseUrl($url)
    {
        if ($url instanceof UriInterface) {
            $url = $url->withPath('')->withQuery('')->withFragment('');
        }

        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Retrieves the URL for the home page.
     *
     * @return string A URL representing the home page.
     */
    public function baseUrl()
    {
        return $this->baseUrl;
    }
}
