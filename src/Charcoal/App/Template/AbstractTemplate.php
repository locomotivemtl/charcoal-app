<?php

namespace Charcoal\App\Template;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Dependencies from `Pimple`
use \Pimple\Container;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractEntity;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\Template\TemplateInterface;

/**
 *
 */
abstract class AbstractTemplate extends AbstractEntity implements
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
        $this->setLogger($data['logger']);

        // @todo: Remove this dependency.
        if (isset($data['app'])) {
            $this->setApp($data['app']);
        }
    }

    /**
     * Give an opportunity to children classes to inject dependencies from a Pimple Container.
     *
     * Does nothing by default, reimplement in children classes.
     *
     * The `$container` DI-container (from `Pimple`) should not be saved or passed around, only to be used to
     * inject dependencies (typically via setters).
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children template classes.
    }

    /**
     * The default Template View is a simple GenericView.
     *
     * @param array $data The optional view data.
     * @return \Charcoal\View\ViewInterface
     */
    public function createView(array $data = null)
    {
        $view = new GenericView([
            'logger' => $this->logger
        ]);
        if ($data !== null) {
            $view->setData($data);
        }
        return $view;
    }
}
