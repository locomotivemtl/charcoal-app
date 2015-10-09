<?php

namespace Charcoal\App\Ui;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\App\Ui\FormInterface;

/**
* FormGroupTrait with basic methods to make
* sure when it's looped in the form widget,
* it has all the basic methods it requires
* to display as expected.
*
* @see FormGroupInterface
*/
trait FormGroupTrait
{
    /**
    * In-memory copy of the parent form widget.
    * @var FormWidget $form
    */
    private $form;

    /**
    * Should always be declared in every widget
    * using that trait.
    * @var string $widget_type
    */
    private $widget_type;

    /**
    * These might never be used in the widget
    * but this will be called by the Form widget
    *
    * @var string $title
    */
    private $title;

    /**
    * @var string $subtitle
    */
    private $subtitle;

    /**
    * Order / sorting is done with the "priority".
    * @var integer $priority
    */
    private $priority = 0;

    /**
    * @param string $type
    * @throws InvalidArgumentException
    * @return FormGroupInterface Chainable
    */
    public function set_widget_type($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Widget type must be a string'
            );
        }
        $this->widget_type = $type;
        return $this;
    }

    /**
    * @return string
    */
    public function widget_type()
    {
        return $this->widget_type;
    }

    /**
    * @param String $title
    * @return $this (chainable)
    */
    public function set_title($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
    * @return String || null
    */
    public function title()
    {
        return $this->title;
    }

    /**
    * @param String $title
    * @return $this (chainable)
    */
    public function set_subtitle($subtitle)
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
    * @return String || null
    */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
    * @param FormInterface $form
    * @return FormGroupWidget Chainable
    */
    public function set_form(FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
    * @return FormInterface or null
    */
    public function form()
    {
        return $this->form;
    }

    /**
    * @var integer $priority
    * @throws InvalidArgumentException
    * @return FormGroupInterface Chainable
    */
    public function set_priority($priority)
    {
        if (!is_int($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }
        $priority = (int)$priority;
        $this->priority = $priority;
        return $this;
    }

    /**
    * @return integer
    */
    public function priority()
    {
        return $this->priority;
    }
}
