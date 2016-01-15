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
     * @var TranslationString $slugPattern
     */
    private $slugPattern;

    /**
     * @var TranslationString $slug
     */
    private $slug;

    /**
     * @param boolean $routable Routable flag, if the object is routable or not.
     * @return RoutableInterface Chainable
     */
    public function setRoutable($routable)
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
     * @param mixed $pattern The slug / URL / route pattern (translation string).
     * @return RoutableInterface Chainable
     */
    public function setSlugPattern($pattern)
    {
        $this->slugPattern = new TranslationString($pattern);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function slugPattern()
    {
        return $this->slugPattern;
    }

    /**
     * @param mixed $slug The slug value (translation string).
     * @return RoutableInterface Chainable
     */
    public function setSlug($slug)
    {
        $this->slug = new TranslationString($slug);
        return $this;
    }

    /**
     * @return string
     */
    public function slug()
    {
        if ($this->slug === null) {
            $this->slug = $this->generateSlug();
        }
        return $this->slug;
    }

    /**
     * Generate a URL slug from the object's URL slug pattern.
     *
     * @return string
     */
    public function generateSlug()
    {
        $pattern = $this->slugPattern();
        if ($this instanceof Viewable) {
            $slug = $this->renderTemplate($pattern);
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
     * Get the route callback that matches a given path (or null).
     *
     * @param string            $path     The URL path to load.
     * @param RequestInterface  $request  The PSR-7 compatible Request instance.
     * @param ResponseInterface $response The PSR-7 compatible Response instance.
     * @return callable|null Route callable
     */
    abstract public function routeHandler($path, RequestInterface $request, ResponseInterface $response);
}
