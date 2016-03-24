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

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\App\Script\AbstractScript', $obj);
    }

    public function testInvoke()
    {
        $request = $this->getMock('\Psr\Http\Message\RequestInterface');
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $invoke = call_user_func([$this->obj, '__invoke'], $request, $response);

    }
}
