<?php

namespace Charcoal\App\Script;

use Traversable;
use RuntimeException;
use InvalidArgumentException;

/**
 * Additional utilities for handling arguments and inputs.
 */
trait ArgScriptTrait
{
    /**
     * Resolve the given value as a collection of values.
     *
     * If the given value is a string, it will be split.
     *
     * @param  array|string $var       An argument to split.
     * @param  string       $delimiter The boundary string.
     * @throws InvalidArgumentException If the value cannot be parsed into an array.
     * @return array|Traversable
     */
    protected function parseAsArray($var, $delimiter = '[\s,]+')
    {
        if (is_string($var)) {
            if (!is_string($delimiter)) {
                throw new InvalidArgumentException('The delimiter must be a string.');
            }

            $var = preg_split('#(?<!\\\\)'.$delimiter.'#', $var);
        }

        if (is_array($var) || $var instanceof Traversable) {
            return $var;
        }

        throw new InvalidArgumentException('The value cannot be split.');
    }

    /**
     * Parse command line arguments into script properties.
     *
     * @throws RuntimeException If a checkbox/radio argument has no options.
     * @return self
     */
    protected function parseArguments()
    {
        $cli  = $this->climate();
        $args = $cli->arguments;

        $ask    = $args->defined('interactive');
        $params = $this->arguments();
        foreach ($params as $key => $param) {
            $setter = $this->setter($key);

            if (!is_callable([ $this, $setter ])) {
                continue;
            }

            $value = $args->get($key);
            if (!empty($value) || is_numeric($value)) {
                $this->{$setter}($value);
            }

            if ($ask) {
                if (isset($param['prompt'])) {
                    $label = $param['prompt'];
                } else {
                    continue;
                }

                $value = $this->input($key);
                if (!empty($value) || is_numeric($value)) {
                    $this->{$setter}($value);
                }
            }
        }

        return $this;
    }
}
