<?php

namespace Charcoal\App\Ui;

/**
*
*/
interface FormInterface
{
    /**
    * @param string $action
    * @throws InvalidArgumentException
    * @return FormInterface Chainable
    */
    public function set_action($action);

    /**
    * @return string
    */
    public function action();

    /**
    * @param string $method Either "post" or "get"
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function set_method($method);

    /**
    * @return string Either "post" or "get"
    */
    public function method();

    /**
    * @param string $url
    * @throws InvalidArgumentException if success is not a boolean
    * @return ActionInterface Chainable
    */
    public function set_next_url($url);

    /**
    * @return bool
    */
    public function next_url();

    /**
    * @param array $groups
    * @return FormInterface Chainable
    */
    public function set_groups(array $groups);

    /**
    * @param string $group_ident
    * @param array|FormGroupInterface
    * @throws InvalidArgumentException
    * @return FormInterface Chainable
    */
    public function add_group($group_ident, $group);

    /**
    * Group generator
    */
    public function groups();

    /**
    * @param array $data
    * @return FormWidget Chainable
    */
    public function set_form_data(array $data);

    /**
    * @param string $key
    * @param mixed $val
    * @throws InvalidArgumentException
    * @return FormWidget Chainable
    */
    public function add_form_data($key, $val);

    /**
    * @return array
    */
    public function form_data();

        /**
    * @param array $properties
    * @return FormInterface Chainable
    */
    public function set_form_properties(array $properties);

    /**
    * @param string $property_ident
    * @param array|FormPropertyWidget
    * @throws InvalidArgumentException
    * @return FormInterface Chainable
    */
    public function add_form_property($property_ident, $property);

    /**
    * @param array|null $data
    * @return FormPropertyInterface
    */
    public function create_form_property(array $data = null);

    /**
    * Properties generator
    */
    public function form_properties();
}
