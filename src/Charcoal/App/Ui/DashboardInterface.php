<?php

namespace Charcoal\App\Ui;

/**
*
*/
interface DashboardInterface
{

    /**
    * @param LayoutInterface|array
    * @return DashboardInterface Chainable
    */
    public function set_layout($layout);

    /**
    * @return LayoutInterface
    */
    public function layout();

    /**
    * @param array $widgets
    * @return DashboardInterface Chainable
    */
    public function set_widgets($widgets);

    /**
    * @param string $widget_ident
    * @param WidgetInterface|array $widget
    * @return DashboardInterface Chainable
    */
    public function add_widget($widget_ident, $widget);

    /**
    * Widgets generator
    */
    public function widgets();

    /**
    * @return boolean
    */
    public function has_widgets();

    /**
    * @return integer
    */
    public function num_widgets();
}
