<?php

namespace Charcoal\App\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

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
