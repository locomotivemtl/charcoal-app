<?php

namespace Charcoal\App\Route;

// From 'charcoal-app'
use Charcoal\App\Route\RouteConfig;

/**
 *
 */
class ActionRouteConfig extends RouteConfig
{

    /**
     * @var array $actionData
     */
    private $actionData = [];

    /**
     * Set the action data.
     *
     * @param array $actionData The route data.
     * @return ActionRouteConfig Chainable
     */
    public function setActionData(array $actionData)
    {
        $this->actionData = $actionData;
        return $this;
    }

    /**
     * Get the action data.
     *
     * @return array
     */
    public function actionData()
    {
        return $this->actionData;
    }
}
