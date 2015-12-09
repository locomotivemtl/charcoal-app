<?php

namespace Charcoals\Tests\Email;

use \Charcoal\App\Email\EmailConfig;

class EmailConfigTest extends \PHPUnit_Framework_Testcase
{
    public function testSetData()
    {
        $obj = new EmailConfig();
        $data = [
            'smtp'=>true,
            'smtp_hostname'=>'localhost',
            'default_from'=>'test@example.com',
            'default_reply_to'=>[
                'name'=>'Test',
                'email'=>'test@example.com'
            ],
            'default_log'=>true,
            'default_track'=>true
        ];
        $ret = $obj->set_data($data);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->smtp());
        $this->assertEquals('localhost', $obj->smtp_hostname());
        $this->assertEquals('test@example.com', $obj->default_from());
        $this->assertEquals('"Test" <test@example.com>', $obj->default_reply_to());
        $this->assertEquals(true, $obj->default_log());
        $this->assertEquals(true, $obj->default_track());
    }

    public function testSetSmtp()
    {
        $obj = new EmailConfig();
        $ret = $obj->set_smtp(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->smtp());

    }

    public function testSetDefaultFrom()
    {
        $obj = new EmailConfig();
        $ret = $obj->set_default_from('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->default_from());

        $obj->set_default_from(
            [
            'name'=>'Test',
            'email'=>'test@example.com'
            ]
        );
        $this->assertEquals('"Test" <test@example.com>', $obj->default_from());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_default_from(123);
    }

    public function testSetDefaultReplyTo()
    {
        $obj = new EmailConfig();
        $ret = $obj->set_default_reply_to('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->default_reply_to());

        $obj->set_default_reply_to(
            [
            'name'=>'Test',
            'email'=>'test@example.com'
            ]
        );
        $this->assertEquals('"Test" <test@example.com>', $obj->default_reply_to());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_default_reply_to(123);
    }

    public function testSetDefaultLog()
    {
        $obj = new EmailConfig();
        $ret = $obj->set_default_log(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->default_log());
    }

    public function testSetDefaultTrack()
    {
        $obj = new EmailConfig();
        $ret = $obj->set_default_track(true);
        $this->assertSame($ret, $obj);
        $this->assertEquals(true, $obj->default_track());

    }
}
