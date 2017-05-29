<?php

namespace Charcoal\App\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From PSR-11
use Psr\Container\ContainerInterface;

/**
 *
 */
interface TemplateInterface
{
    /**
     * Set dependencies from the service locator.
     *
     * @param  ContainerInterface $container A service locator.
     * @return void
     */
    public function setDependencies(ContainerInterface $container);

    /**
     * @param array $data The template data to set.
     * @return TemplateInterface Chainable
     */
    public function setData(array $data);

    /**
     * Initialize the template with a request.
     *
     * @param RequestInterface $request The request to intialize.
     * @return boolean
     */
    public function init(RequestInterface $request);
}
