<?php

namespace Charcoal\App\Template;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\AppInterface;
use \Charcoal\App\Template\TemplateInterface;

/**
 *
 */
abstract class AbstractTemplate implements
    AppAwareInterface,
    LoggerAwareInterface,
    TemplateInterface,
    ViewableInterface
{
    use AppAwareTrait;
    use ViewableTrait;
    use LoggerAwareTrait;

    /**
     * @param array $data The dependencies (app and logger).
     */
    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }

        $this->set_app($data['app']);
    }

    /**
     * @param array $data The data array (as [key=>value] pair) to set.
     * @return AbtractTemplate Chainable
     */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];

            if ($val === null) {
                continue;
            }

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
            'logger' => $this->logger
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }
}
