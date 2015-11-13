<?php

namespace Charcoal\App\Action;

interface ActionInterface
{

    /**
    * @param string $mode
    * @return ActionInterface Chainable
    */
    public function set_mode($mode);

    /**
    * @return string
    */
    public function mode();

    /**
    * @param boolean $success
    * @return ActionInterface Chainable
    */
    public function set_success($success);

    /**
    * @return boolean
    */
    public function success();

    /**
    * @return array
    */
    public function response();
}
