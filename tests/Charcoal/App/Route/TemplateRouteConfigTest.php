<?php

namespace Charcoal\Tests\App\Route;

use \Charcoal\App\Route\TemplateRouteConfig;

class TemplateRouteConfigTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new TemplateRouteConfig();
    }

    // public function testSetEngine()
    // {
    //     $this->assertEquals('mustache', $this->obj->engine());
    //     $ret = $this->obj->setEngine('twig');
    //     $this->assertSame($ret, $this->obj);
    //     $this->assertEquals('twig', $this->obj->engine());
    // }

    public function testSetRedirectMode()
    {
        $this->assertEquals(301, $this->obj->redirectMode());
        $ret = $this->obj->setRedirectMode(302);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(302, $this->obj->redirectMode());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setRedirectMode(666);

    }

    public function testSetCacheTtl()
    {
        $this->assertEquals(0, $this->obj->cacheTtl());
        $ret = $this->obj->setCacheTtl('42');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->cacheTtl());
    }
}
