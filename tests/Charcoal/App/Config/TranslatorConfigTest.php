<?php

namespace Charcoal\Tests\App\Config;

use \Pimple\Container;

use \Charcoal\App\Config\TranslatorConfig;

/**
 *
 */
class TranslatorConfigTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new TranslatorConfig();
    }

    public function testDefaults()
    {
        $this->assertEquals('.', $this->obj->separator());

        $this->assertTrue($this->obj->active());
        $this->assertEquals(['noop'], $this->obj->types());

        $this->assertArrayHasKey('repositories', $this->obj->locales());
        $this->assertArrayHasKey('languages', $this->obj->locales());

        $this->assertEquals(['vendor/locomotivemtl/charcoal-translation/config/languages.json'], $this->obj['locales.repositories']);
        $this->assertEquals([], $this->obj['translations.paths']);
        $this->assertEquals([], $this->obj['translations.messages']);
    }

    public function testSetActive()
    {
        $ret = $this->obj->setActive(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->active());
    }

    public function testSetTypes()
    {
        $ret = $this->obj->setTypes(['db', 'file']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['db', 'file'], $this->obj->types());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setTypes(['foobar']);
    }

    public function testSetCurrentLanguage()
    {
        $this->obj->setCurrentLanguage('fr');
    }
}
