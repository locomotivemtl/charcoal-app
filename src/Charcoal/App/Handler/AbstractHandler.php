<?php

namespace Charcoal\App\Handler;

use RuntimeException;

// From PSR-3
use Psr\Log\LoggerAwareTrait;

// From PSR-7
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

// From Slim
use Slim\Http\Body;

// From Pimple
use Pimple\Container;

// From 'charcoal-config'
use Charcoal\Config\ConfigurableTrait;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-view'
use Charcoal\View\ViewInterface;
use Charcoal\View\ViewableTrait;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-app'
use Charcoal\App\Handler\HandlerConfig;
use Charcoal\App\Handler\HandlerInterface;
use Charcoal\App\Handler\TemplateableHandlerTrait;

/**
 * Abstract Charcoal Application Handler
 *
 * Enhanced version Slim's application handler.
 *
 * It outputs messages in either JSON, XML or HTML
 * based on the Accept header.
 */
abstract class AbstractHandler implements
    HandlerInterface
{
    const DEFAULT_PARTIAL = null;

    use ConfigurableTrait;
    use LoggerAwareTrait;
    use TemplateableHandlerTrait;
    use TranslatorAwareTrait;
    use ViewableTrait;

    /**
     * Store the factory instance.
     *
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * Store the HTTP request object.
     *
     * @var ServerRequestInterface
     */
    protected $httpRequest;

    /**
     * The service locator.
     *
     * @var Container
     */
    protected $container;

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
        'text/plain',
    ];

    /**
     * Return a new AbstractHandler object.
     *
     * @param Container          $container A dependencies container instance.
     * @param array|\Traversable $config    A handler configset.
     */
    public function __construct(Container $container, $config = null)
    {
        $this->setContainer($container);
        $this->setDependencies($container);

        if ($config !== null) {
            $this->config()->merge($config);
        }
    }

    /**
     * String representation of the handler.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSummary();
    }

    /**
     * Initialize the AbstractHandler object.
     *
     * @return self
     */
    public function init()
    {
        $this->resolveTemplatePartial();

        return $this;
    }

    /**
     * Create a configset for the handler.
     *
     * @see    ConfigurableTrait::createConfig()
     * @param  mixed|null $data Optional config data.
     * @return \Charcoal\Config\ConfigInterface
     */
    final public function createConfig($data = null)
    {
        return new HandlerConfig($data);
    }

    /**
     * Retrieve the handler's code.
     *
     * @return string|integer
     */
    public function getCode()
    {
        return 0;
    }

    /**
     * Retrieve the handler's summary.
     *
     * @return string
     */
    abstract public function getSummary();

    /**
     * Retrieve the handler's message.
     *
     * @return string
     */
    abstract public function getMessage();

    /**
     * Set dependencies from the service locator.
     *
     * @param  Container $container A service locator.
     * @return self
     */
    protected function setDependencies(Container $container)
    {
        $this->setTranslator($container['translator']);
        $this->setView($container['view']);
        $this->setTemplateFactory($container['template/factory']);
        if (isset($container['config']['handlers.defaults'])) {
            $this->setConfig($container['config']['handlers.defaults']);
        }
        return $this;
    }


    /**
     * Retrieve the template factory.
     *
     * @throws RuntimeException If the template factory is missing.
     * @return FactoryInterface
     */
    final protected function templateFactory()
    {
        if ($this->templateFactory === null) {
            throw new RuntimeException(sprintf(
                'Template Factory is not defined for [%s]',
                get_class($this)
            ));
        }

        return $this->templateFactory;
    }

    /**
     * Set an HTTP request object.
     *
     * @param  ServerRequestInterface $request The most recent Request object.
     * @return self
     */
    final protected function setHttpRequest(ServerRequestInterface $request)
    {
        $this->httpRequest = $request;

        return $this;
    }

    /**
     * Retrieve the HTTP request.
     *
     * @used-by \Charcoal\App\Template\TemplateInterface::init()
     *     Via {@see TemplateableHandlerTrait::renderHtmlTemplateContent()}
     * @throws  RuntimeException If the HTTP request was not previously set.
     * @return  ServerRequestInterface
     */
    final protected function httpRequest()
    {
        if ($this->httpRequest === null) {
            throw new RuntimeException(sprintf(
                'HTTP Request is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->httpRequest;
    }


    /**
     * Determine which content type we know about is wanted using "Accept" header
     *
     * @see    \Slim\Handlers::determineContentType()
     * @param  ServerRequestInterface $request The most recent Request object.
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request)
    {
        if (PHP_SAPI === 'cli') {
            return 'text/plain';
        }

        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);

        if (count($selectedContentTypes)) {
            return current($selectedContentTypes);
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
     * Mutate the given response.
     *
     * @param  ResponseInterface $response    The most recent Response object.
     * @param  string            $contentType The content type of the output.
     * @param  string            $output      The text output.
     * @return ResponseInterface
     */
    protected function respondWith(ResponseInterface $response, $contentType, $output)
    {
        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response->withHeader('Content-Type', $contentType)
            ->withBody($body);
    }

    /**
     * Set an template factory.
     *
     * @param  FactoryInterface $factory The factory to create templates.
     * @return void
     */
    private function setTemplateFactory(FactoryInterface $factory)
    {
        $this->templateFactory = $factory;
    }

    /**
     * Set container for use with the template controller
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    private function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
