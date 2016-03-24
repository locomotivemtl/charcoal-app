<?php

namespace Charcoal\Tests\App\Script;

use \Charcoal\App\App;

class AbstractScriptTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\App\Script\AbstractScript', [[
                'app'=>$GLOBALS['app'],
                'logger'=>new \Psr\Log\NullLogger()
            ]]);
    }

    public function testInvoke()
    {
        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $invoke = call_user_func([$this->obj, '__invoke'], $request, $response);

    }

    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());
    }

    public function testSetDescription()
    {
        $ret = $this->obj->setDescription('Foo Description');
        $this->assertSame($ret, $this->obj);
        $this->assertEQuals('Foo Description', $this->obj->description());
    }

    public function testSetQuiet()
    {
        $this->assertFalse($this->obj->quiet());
        $ret = $this->obj->setQuiet(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->quiet());
    }

    public function testSetVerbose()
    {
        $this->assertFalse($this->obj->verbose());
        $ret = $this->obj->setVerbose(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->verbose());
    }

    public function testSetArguments()
    {
        $defaultArgs = $this->obj->arguments();
        $ret = $this->obj->setArguments([
            'foo'=>[]
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertArrayHasKey('foo', $this->obj->arguments());
        $this->assertEquals([], $this->obj->argument('foo'));
    }
}
