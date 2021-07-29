<?php

namespace Charcoal\App\Config;

use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Logger Configuration
 */
class LoggerConfig extends AbstractConfig
{
    const DEFAULT_CHANNEL = 'charcoal';

    /**
     * Whether to enable or disable the logger service.
     *
     * @var boolean
     */
    private $active;

    /**
     * Record handler(s) to use.
     *
     * Whenever you add a record to the logger, it traverses the handler stack.
     *
     * @var array
     */
    private $handlers;

    /**
     * Record processor(s) to use.
     *
     * For customizing records added to the logger.
     *
     * @var array
     */
    private $processors;

    /**
     * Channel name.
     *
     * @var string
     */
    private $channel = self::DEFAULT_CHANNEL;

    /**
     * Retrieve the default values.
     *
     * @return array
     */
    public function defaults()
    {
        return [
            'active'    => true,
            'channel'   => self::DEFAULT_CHANNEL,
            'level'     => 'debug',
            'handlers'  => [
                'stream' => [
                    'type'   => 'stream',
                    'stream' => '../logs/charcoal.app.log',
                    'level'  => null,
                    'bubble' => true,
                    'active' => true,
                ],
                'console' => [
                    'type'   => 'browser-console',
                    'level'  => null,
                    'active' => false,
                ]
            ],
            'processors' => [
                [
                    'type' => 'memory-usage',
                ],
                [
                    'type' => 'uid',
                ],
            ],
        ];
    }

    /**
     * Enable / Disable the logger service.
     *
     * @param  boolean $active The active flag;
     *     TRUE to enable, FALSE to disable.
     * @return LoggerConfig Chainable
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * Determine if the logger service is enabled.
     *
     * @return boolean TRUE if enabled, FALSE if disabled.
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * Set the record handler(s) to use.
     *
     * @param  array $handlers One or more (Monolog) record handlers; used as a stack.
     * @return self
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = [];
        $this->addHandlers($handlers);
        return $this;
    }

    /**
     * Add record handler(s) to use.
     *
     * @param  string[] $handlers One or more (Monolog) handlers to stack.
     * @return self
     */
    public function addHandlers(array $handlers)
    {
        foreach ($handlers as $key => $handler) {
            $this->addHandler($handler, $key);
        }
        return $this;
    }

    /**
     * Add a record handler to use.
     *
     * @param  array       $handler The record handler structure.
     * @param  string|null $key     The handler's key.
     * @throws InvalidArgumentException If the handler is invalid.
     * @return self
     */
    public function addHandler(array $handler, $key = null)
    {
        if (!isset($handler['type'])) {
            throw new InvalidArgumentException(
                'Handler type is required.'
            );
        }

        if (!is_string($key)) {
            $this->handlers[] = $handler;
        } else {
            $this->handlers[$key] = $handler;
        }

        return $this;
    }

    /**
     * Retrieve the record handler(s) to use.
     *
     * @return array
     */
    public function handlers()
    {
        return $this->handlers;
    }

    /**
     * Set the record processor(s) to use.
     *
     * @param  array $processors One or more (Monolog) record processors; used as a stack.
     * @return self
     */
    public function setProcessors(array $processors)
    {
        $this->processors = [];
        $this->addProcessors($processors);
        return $this;
    }

    /**
     * Add record processor(s) to use.
     *
     * @param  string[] $processors One or more (Monolog) processors to stack.
     * @return self
     */
    public function addProcessors(array $processors)
    {
        foreach ($processors as $key => $processor) {
            $this->addProcessor($processor, $key);
        }
        return $this;
    }

    /**
     * Add a record processor to use.
     *
     * @param  array       $processor The record processor structure.
     * @param  string|null $key       The processor's key.
     * @throws InvalidArgumentException If the processor is invalid.
     * @return self
     */
    public function addProcessor(array $processor, $key = null)
    {
        if (!isset($processor['type'])) {
            throw new InvalidArgumentException(
                'Processor type is required.'
            );
        }

        if (!is_string($key)) {
            $this->processors[] = $processor;
        } else {
            $this->processors[$key] = $processor;
        }

        return $this;
    }

    /**
     * Retrieve the record processor(s) to use.
     *
     * @return array
     */
    public function processors()
    {
        return $this->processors;
    }

    /**
     * Set the channel name.
     *
     * @param  string $name The channe name (namespace).
     * @throws InvalidArgumentException If the channel name is not a string.
     * @return self
     */
    public function setChannel($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                'Channel name must be a string.'
            );
        }

        $this->channel = $name;
        return $this;
    }

    /**
     * Retrieve the cache namespace.
     *
     * @return string
     */
    public function channel()
    {
        return $this->channel;
    }
}
