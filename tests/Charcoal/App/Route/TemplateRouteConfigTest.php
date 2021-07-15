<?php

namespace Charcoal\Tests\App\Route;

use Charcoal\App\Route\TemplateRouteConfig;
use Charcoal\Tests\AbstractTestCase;

class TemplateRouteConfigTest extends AbstractTestCase
{
    public $obj;

    public function setUp(): void
    {
        $this->obj = new TemplateRouteConfig();
    }

    public function testSetEngine()
    {
        //$this->assertEquals('mustache', $this->obj->engine());
        $ret = $this->obj->setEngine('twig');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('twig', $this->obj->engine());

        $this->obj->setEngine(null);
        //$this->assertEquals('mustache', $this->obj->engine());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setEngine(false);
    }

    public function testSetTemplate()
    {
        $this->assertNull($this->obj->template());
        $ret = $this->obj->setTemplate('foobar');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('foobar', $this->obj->template());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setTemplate(false);
    }

    public function testRedirect()
    {
        $this->assertNull($this->obj->redirect());
        $ret = $this->obj->setRedirect('foobar');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('foobar', $this->obj->redirect());
    }

    public function testSetRedirectMode()
    {
        $this->assertEquals(301, $this->obj->redirectMode());
        $ret = $this->obj->setRedirectMode(302);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(302, $this->obj->redirectMode());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setRedirectMode(666);
    }

    public function testSetCache()
    {
        $this->assertFalse($this->obj->cache());
        $ret = $this->obj->setCache(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->cache());
    }

    public function testSetCacheTtl()
    {
        $this->assertEquals(0, $this->obj->cacheTtl());
        $ret = $this->obj->setCacheTtl('42');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->cacheTtl());
    }
}
