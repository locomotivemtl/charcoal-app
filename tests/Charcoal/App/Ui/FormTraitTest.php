<?php

namespace Charcoal\Tests\App\Ui;

use \Charcoal\App\Ui\FormTrait;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForTrait('\Charcoal\App\Ui\FormTrait');
    }

    /**
    * Assert that the `set_action()` method:
    * - is chainable
    * - sets the action
    * - throws an exception if the parameter is not a string
    * and that the `action()` method
    * - defaults to ""
    */
    public function testSetAction()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->action());
        $ret = $obj->set_action('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->action());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_action(true);
    }

    /**
    * Assert that the `set_method()` method:
    * - is chainable
    * - sets the method
    * - throws an exception if the parameter is not a string
    * and that the `method()` method
    * - defaults to "post"
    */
    // public function testSetMethod()
    // {
    // 	$obj = $this->obj;
    // 	$this->assertEquals('post', $obj->method());
    // 	$ret = $obj->set_method('get');
    // 	$this->assertSame($ret, $obj);
    // 	$this->assertEquals('get', $obj->method());

    // 	$this->setExpectedException('\InvalidArgumentException');
    // 	$obj->set_method('foo');
    // }

    /**
    * Assert that the `set_next_url()` method:
    * - is chainable
    * - sets the action
    * - throws an exception if the parameter is not a string
    * and that the `next_url()` method
    * - defaults to ""
    */
    public function testSetNextUrl()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->next_url());
        $ret = $obj->set_next_url('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->next_url());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_next_url(true);
    }
}
