<?php

namespace Charcoal\App\Routable;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependencies from `charcoal-view` module
use \Charcoal\View\Viewable;

use \Charcoal\Translation\TranslationString;

/**
* Full implementation, as Trait, of the `RoutableInterface`.
*/
trait RoutableTrait
{
    /**
    * @var boolean routable
    */
    private $routable = true;

    /**
    * @var string
    */
    private $slug_pattern = '';

    /**
    * @var string $slug
    */
    private $slug;

    /**
    * @param boolean $data
    * @return RoutableInterface Chainable
    */
    public function set_routable($routable)
    {
        $this->routable = !!$routable;
        return $this;
    }

    /**
    * @return boolean
    */
    public function routable()
    {
        return $this->routable;
    }

    /**
    * @param mixed $url
    * @return RoutableInterface Chainable
    */
    public function set_slug_pattern($pattern)
    {
        $this->slug_pattern = new TranslationString($pattern);
        return $this;
    }

    /**
    * @return TranslationString
    */
    public function slug_pattern()
    {
        return $this->slug_pattern;
    }

    /**
    * @param mixed $slug
    * @return RoutableInterface Chainable
    */
    public function set_slug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
    * @return string
    */
    public function slug()
    {
        if ($this->slug === null) {
            $this->slug = $this->generate_slug();
        }
        return $this->slug;
    }

    /**
    * Generate a URL slug from the object's URL slug pattern.
    */
    public function generate_slug()
    {
        $pattern = $this->slug_pattern();
        if ($this instanceof Viewable) {
            $slug = $this->render($pattern);
        } else {
            $slug = $pattern;
        }
        return $slug;
    }

    /**
    * @return string
    */
    public function url()
    {
        return $this->slug();
    }
    
    /**
    * @param string $path
    * @param RequestInterface $request
    * @param ResponseInterface $response
    * @return callable|null Route callable
    */
    abstract public function handle_route($slug, RequestInterface $request, ResponseInterface $response);
}
