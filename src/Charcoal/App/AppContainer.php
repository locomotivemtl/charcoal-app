<?php

namespace Charcoal\App;

// From Slim
use Slim\Container;

// From Pimple
use Pimple\ServiceProviderInterface;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\ServiceProvider\AppServiceProvider;

/**
 * Charcoal App Container
 */
class AppContainer extends Container
{
    /**
     * Create new container
     *
     * @param array $values The parameters or objects.
     */
    public function __construct(array $values = [])
    {
        // Initialize container for Slim and Pimple
        parent::__construct($values);

        // Ensure "config" is set
        $this['config'] = (isset($values['config']) ? $values['config'] : new AppConfig());

        $this->register(new AppServiceProvider());

        $this->registerProviderFactory();
        $this->registerConfigProviders();
    }

    /**
     * @return void
     */
    private function registerProviderFactory()
    {
        /**
        * @return Factory
        */
        if (!isset($this['provider/factory'])) {
            $this['provider/factory'] = function () {
                return new Factory([
                    'base_class'       => ServiceProviderInterface::class,
                    'resolver_options' => [
                        'suffix' => 'ServiceProvider'
                    ]
                ]);
            };
        }
    }

    /**
     * @return void
     */
    private function registerConfigProviders()
    {
        if (empty($this['config']['service_providers'])) {
            return;
        }

        $providers = $this['config']['service_providers'];

        foreach ($providers as $provider => $values) {
            if (false === $values) {
                continue;
            }

            if (!is_array($values)) {
                $values = [];
            }

            $provider = $this['provider/factory']->create($provider);

            $this->register($provider, $values);
        }
    }
}
