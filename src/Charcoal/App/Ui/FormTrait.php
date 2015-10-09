<?php

namespace Charcoal\App\Ui;

use \InvalidArgumentException;

use \Charcoal\Widget\WidgetFactory;

/**
*
*/
trait FormTrait
{
    /**
    * @var string $action
    */
    private $action = '';

    /**
    * @var string $method
    */
    private $method = 'post';

    /**
    * @var string $next_url
    */
    private $next_url = '';

    /**
    * @var array $groups
    */
    protected $groups = [];

    /**
    * @var array $form_data
    */
    private $form_data = [];
    /**
    * @var array $form_properties
    */
    private $form_properties = [];

    /**
    * @param string $action
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function set_action($action)
    {
        if (!is_string($action)) {
            throw new InvalidArgumentException(
                'Action must be a string'
            );
        }
        $this->action = $action;
        return $this;
    }

    /**
    * @return string
    */
    public function action()
    {
        return $this->action;
    }

    /**
    * @param string $method Either "post" or "get"
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function set_method($method)
    {
        $method = strtolower($method);
        if (!in_array($method, ['post', 'get'])) {
            throw new InvalidArgumentException(
                'Method must be "post" or "get"'
            );
        }
        $this->method = $method;
        return $this;
    }

    /**
    * @return string Either "post" or "get"
    */
    public function method()
    {
        return $this->method;
    }

    /**
    * @param string $url
    * @throws InvalidArgumentException if success is not a boolean
    * @return ActionInterface Chainable
    */
    public function set_next_url($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'URL needs to be a string'
            );
        }

        $this->next_url = $url;
        return $this;
    }

    /**
    * @return bool
    */
    public function next_url()
    {
        return $this->next_url;
    }

    /**
    * @param array $groups
    * @return FormInterface Chainable
    */
    public function set_groups(array $groups)
    {
        $this->groups = [];
        foreach ($groups as $group_ident => $group) {
            $this->add_group($group_ident, $group);
        }
        return $this;
    }

    /**
    * @param string $group_ident
    * @param array|FormGroupInterface
    * @throws InvalidArgumentException
    * @return FormInterface Chainable
    */
    public function add_group($group_ident, $group)
    {
        if (!is_string($group_ident)) {
            throw new InvalidArgumentException(
                'Group ident must be a string'
            );
        }

        if (($group instanceof FormGroupInterface)) {
            $group->set_form($this);
            $this->groups[$group_ident] = $group;
        } else if (is_array($group)) {
            $g = $this->create_group($group);
            $this->groups[$group_ident] = $g;
        } else {
            throw new InvalidArgumentException(
                'Group must be a Form Group object or an array'
            );
        }

        return $this;
    }

    /**
    * @param array|null $data
    * @return FormGroupInterface
    */
    abstract public function create_group(array $data = null);

    /**
    * Group generator
    */
    public function groups()
    {
        $groups = $this->groups;
        if (!is_array($this->groups)) {
            yield null;
        } else {
            uasort($groups, ['self', 'sort_groups_by_priority']);
            foreach ($groups as $group) {
                $GLOBALS['widget_template'] = $group->widget_type();
                yield $group->ident() => $group;
            }
        }
    }

    /**
    * To be called with uasort()
    *
    * @param FormGroupInterface $a
    * @param FormGroupInterface $b
    * @return integer Sorting value: -1, 0, or 1
    */
    static protected function sort_groups_by_priority(FormGroupInterface $a, FormGroupInterface $b)
    {
        $a = $a->priority();
        $b = $b->priority();

        if ($a == $b) {
            return 1; // Should be 0?
        }

        return ($a < $b) ? (-1) : 1;
    }

    /**
    * @param array $form_data
    * @return FormWidget Chainable
    */
    public function set_form_data(array $form_data)
    {
        $this->form_data = $form_data;
        return $this;
    }

    /**
    * @param string $key
    * @param mixed $val
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function add_form_data($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Key must be a string'
            );
        }
        $this->form_data[$key] = $val;
        return $this;
    }

    /**
    * @return array
    */
    public function form_data()
    {
        return $this->form_data;
    }

    /**
    * @param array $properties
    * @return FormInterface Chainable
    */
    public function set_form_properties(array $properties)
    {
        $this->form_properties = [];
        foreach ($properties as $property_ident => $property) {
            $this->add_form_property($property_ident, $property);
        }
        return $this;
    }

    /**
    * @param string $property_ident
    * @param array|FormPropertyWidget
    * @throws InvalidArgumentException
    * @return FormInterface Chainable
    */
    public function add_form_property($property_ident, $property)
    {
        if (!is_string($property_ident)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        if (($property instanceof FormPropertyInterface)) {
            $this->form_properties[$property_ident] = $property;
        } else if (is_array($property)) {
            $p = $this->create_form_property($property);
            $p->set_property_ident($property_ident);
            $this->form_properties[$property_ident] = $p;
        } else {
            throw new InvalidArgumentException(
                'Property must be a FormProperty object or an array'
            );
        }

        return $this;
    }

    /**
    * @param array|null $data
    * @return FormPropertyInterface
    */
    abstract public function create_form_property(array $data = null);

    /**
    * Properties generator
    */
    public function form_properties()
    {
        $sidebars = $this->sidebars;
        if (!is_array($this->sidebars)) {
            yield null;
        } else {
            foreach ($this->form_properties as $prop) {
                if ($prop->active() === false) {
                    continue;
                }
                $GLOBALS['widget_template'] = $prop->input_type();
                yield $prop->property_ident() => $prop;
            }
        }
    }
}
