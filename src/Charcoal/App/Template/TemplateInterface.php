<?php

namespace Charcoal\App\Template;

// PSR-7 (HTTP Messaging) dependencies
use \Psr\Http\Message\RequestInterface;

// Dependencies from `Pimple`
use \Pimple\Container;

/**
 *
 */
interface TemplateInterface
{
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
