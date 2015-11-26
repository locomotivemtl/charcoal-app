<?php

namespace Charcoal\App\Action;

interface ActionInterface
{

    /**
     * @param string $mode The action mode.
     * @return ActionInterface Chainable
     */
    public function set_mode($mode);

    /**
     * @return string
     */
    public function mode();

    /**
     * @param boolean $success Success flag (true / false).
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
