<?php

namespace Charcoal\App\Script;

/**
* Script are actions called from the CLI.
*
* Typically, with the `charcoal` bin.
*/
interface ScriptInterface
{

    /**
    * @param string $ident
    * @return ScriptInterface Chainable
    */
    public function set_ident($ident);
    
    /**
    * @return string
    */
    public function ident();

    /**
    * @param string $description
    * @return ScriptInterface Chainable
    */
    public function set_description($description);

    /**
    * @return string
    */
    public function description();
    
    /**
    * @param array $arguments
    * @return ScriptInterface Chainable
    */
    public function set_arguments($arguments);
    /**
    * @param string $argument_ident
    * @param array  $argument
    * @return ScriptInterface Chainable
    */
    public function add_argument($argument_ident, $argument);

    /**
    * @return array $arguments
    */
    public function arguments();

    /**
    * @param string $argument_ident
    * @return array
    */
    public function argument($argument_ident);

    /**
    * @param string $arg_name
    * @return array
    */
    public function arg_or_input($arg_name);

    /**
    * @return string
    */
    public function help();
}
