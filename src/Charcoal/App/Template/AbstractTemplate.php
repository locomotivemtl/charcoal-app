<?php

namespace Charcoal\App\Template;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\App;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;
use \Charcoal\App\Template\TemplateInterface;

/**
 *
 */
abstract class AbstractTemplate implements
    LoggerAwareInterface,
    TemplateInterface,
    ViewableInterface
{

    use ViewableTrait;
    use LoggerAwareTrait;

    /**
     * @var App $app
     */
    private $app;

    /**
     * @param array $data The dependencies (app and logger).
     */
    public function __construct(array $data = null)
    {
        $this->set_logger($data['logger']);
        $this->set_app($data['app']);
    }

    /**
     * @param App $app The template's parent charcoal app instance.
     * @return App Chainable
     */
    public function set_app(App $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return App
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * The default Template View is a simple GenericView.
     *
     * @param array $data The data array (as [key=>value] pair) to set.
     * @return AbtractTemplate Chainable
     */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
            } else {
                $this->{$prop} = $val;
            }
        }

        // Chainable
        return $this;
    }

    /**
     * The default Template View is a simple GenericView.
     *
     * @param array $data The optional view data.
     * @return \Charcoal\View\ViewInterface
     */
    public function create_view(array $data = null)
    {
        $view = new GenericView([
            'logger'=>$this->logger()
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
