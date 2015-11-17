<?php

namespace Charcoal\App\Routable;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
* Routable objects are loadable from a URL.
*/
interface RoutableInterface
{
    /**
    * @param boolean $data
    * @return RoutableInterface Chainable
    */
    public function set_routable($routable);

    /**
    * @return boolean
    */
    public function routable();

        /**
    * @param mixed $url
    * @return RoutableInterface Chainable
    */
    public function set_slug_pattern($pattern);

    /**
    * @return string
    */
    public function slug_pattern();

    /**
    * @param mixed $slug
    * @return RoutableInterface Chainable
    */
    public function set_slug($slug);

    /**
    * @return string
    */
    public function slug();

    /**
    * Generate a URL slug from the object's URL slug pattern.
    */
    public function generate_slug();

    /**
    * @return string
    */
    public function url();

    /**
    * @param string $path
    * @param RequestInterface $request
    * @param ResponseInterface $response
    * @return callable|null Route callable
    */
    public function handle_route($path, RequestInterface $request, ResponseInterface $response);
}
