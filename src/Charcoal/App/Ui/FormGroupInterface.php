<?php

namespace Charcoal\App\Ui;

// Local namespace dependencies
use \Charcoal\App\Ui\FormInterface;

/**
*
*/
interface FormGroupInterface
{
    /**
    * All FormGroup-s should have a form associated
    * @param FormInterface
    * @return FormGroupInterface
    */
    public function set_form(FormInterface $form);

    /**
    * @return FormWidget
    */
    public function form();

    /**
    * This should really be in the WidgetInterface...
    * @return string widget type
    */
    public function widget_type();

    /**
    * @param string $title
    * @return FormGroupInterface Chainable
    */
    public function set_title($data);

    /**
    * @return String
    */
    public function title();

    /**
    * @param string $subtitle
    * @return FormGroupInterface Chainable
    */
    public function set_subtitle($data);

    /**
    * @return String
    */
    public function subtitle();

    /**
    * @var integer $priority
    * @throws InvalidArgumentException
    * @return FormGroupWidget Chainable
    */
    public function set_priority($priority);

    /**
    * @return Integer
    */
    public function priority();
}
