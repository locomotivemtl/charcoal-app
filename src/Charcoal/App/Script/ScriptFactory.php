<?php

namespace Charcoal\App\Script;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 * The ScriptFactory creates Script (CLI Action) objects
 */
class ScriptFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\App\Script\ScriptInterface';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Script';
    }
}
