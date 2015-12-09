<?php

namespace Charcoal\Tests\App\Email;

use \Charcoal\App\Email\Email as Email;

/**
* Test the AbstractEmail methods, through concrete `Email` class.
*/
class EmailTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setup()
    {
        // GLOBALS['app'] is defined in bootstrap file
        $this->obj = new Email([
            'app'=>$GLOBALS['app'],
            'logger'=>$GLOBALS['app']->logger()
        ]);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
            [
            'campaign'=>'foo',
            'to'=>'test@example.com',
            'cc'=>'cc@example.com',
            'bcc'=>'bcc@example.com',
            'from'=>'from@example.com',
            'reply_to'=>'reply@example.com',
            'subject'=>'bar',
            'msg_html'=>'foo',
            'msg_txt'=>'baz',
            'attachments'=>[
                'foo'
            ],
            'log'=>true,
            'track'=>true
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->campaign());
        $this->assertEquals(['test@example.com'], $obj->to());
        $this->assertEquals(['cc@example.com'], $obj->cc());
        $this->assertEquals(['bcc@example.com'], $obj->bcc());
        $this->assertEquals('from@example.com', $obj->from());
        $this->assertEquals('reply@example.com', $obj->reply_to());
        $this->assertEquals('bar', $obj->subject());
        $this->assertEquals('foo', $obj->msg_html());
        $this->assertEquals('baz', $obj->msg_txt());
        $this->assertEquals(['foo'], $obj->attachments());
        $this->assertEquals(true, $obj->log());
        $this->assertEquals(true, $obj->track());
    }

    public function testSetCampaign()
    {
        $obj = $this->obj;
        $ret = $obj->set_campaign('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->campaign());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_campaign([1, 2, 3]);
    }

    public function testGenerateCampaign()
    {
        $obj = $this->obj;
        $ret = $obj->campaign();
        $this->assertNotEmpty($ret);
    }

    /**
    * Asserts that the `set_to()` method:
    * - Sets the "to" recipient when using an array of string
    * - Sets the "to" recipient properly when using an email structure (array)
    * - Sets the "to" recipient to an array when setting a single email string
    * - Resets the "to" value before setting it, at every call.
    * - Throws an exception if the to argument is not a string.
    */
    public function testSetTo()
    {
        $obj = $this->obj;

        $ret = $obj->set_to(['test@example.com', 'test2@example.com']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com', 'test2@example.com'], $obj->to());

        $obj->set_to(
            [[
            'name'=>'Test',
            'email'=>'test@example.com'
            ]]
        );
        $this->assertEquals(['"Test" <test@example.com>'], $obj->to());

        $obj->set_to('test@example.com');
        $this->assertEquals(['test@example.com'], $obj->to());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_to(false);
    }

    public function testAddTo()
    {
        $obj = $this->obj;
        $ret = $obj->add_to('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->to());

        $obj->add_to(['name'=>'Test','email'=>'test@example.com']);
        $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->to());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_to(false);
    }

    public function testSetCc()
    {
        $obj = $this->obj;

        $ret = $obj->set_cc(['test@example.com']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->cc());

        $obj->set_cc(
            [[
            'name'=>'Test',
            'email'=>'test@example.com'
            ]]
        );
        $this->assertEquals(['"Test" <test@example.com>'], $obj->cc());

        $obj->set_cc('test@example.com');
        $this->assertEquals(['test@example.com'], $obj->cc());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_cc(false);
    }

    public function testAddCc()
    {
        $obj = $this->obj;
        $ret = $obj->add_cc('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->cc());

        $obj->add_cc(['name'=>'Test','email'=>'test@example.com']);
        $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->cc());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_cc(false);
    }

    public function testSetBcc()
    {
        $obj = $this->obj;

        $ret = $obj->set_bcc(['test@example.com']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->bcc());

        $obj->set_bcc(
            [[
            'name'=>'Test',
            'email'=>'test@example.com'
            ]]
        );
        $this->assertEquals(['"Test" <test@example.com>'], $obj->bcc());

        $obj->set_bcc('test@example.com');
        $this->assertEquals(['test@example.com'], $obj->bcc());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_bcc(false);
    }

    public function testAddBcc()
    {
        $obj = $this->obj;
        $ret = $obj->add_bcc('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test@example.com'], $obj->bcc());

        $obj->add_bcc(['name'=>'Test','email'=>'test@example.com']);
        $this->assertEquals(['test@example.com', '"Test" <test@example.com>'], $obj->bcc());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_bcc(false);
    }

    public function testSetFrom()
    {
        $obj = $this->obj;
        //$config = $obj->config()->set_default_from('default@example.com');
        //$this->assertEquals('default@example.com', $obj->from());

        $ret = $obj->set_from('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->from());

        $obj->set_from(
            [
            'name'=>'Test',
            'email'=>'test@example.com'
            ]
        );
        $this->assertEquals('"Test" <test@example.com>', $obj->from());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_from(false);
    }

    public function testSetReplyTo()
    {
        $obj = $this->obj;
        //$config = $obj->config()->set_default_reply_to('default@example.com');
        //$this->assertEquals('default@example.com', $obj->reply_to());

        $ret = $obj->set_reply_to('test@example.com');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test@example.com', $obj->reply_to());

        $obj->set_reply_to(
            [
            'name'=>'Test',
            'email'=>'test@example.com'
            ]
        );
        $this->assertEquals('"Test" <test@example.com>', $obj->reply_to());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_reply_to(false);
    }

    public function testSetSubject()
    {
        $obj = $this->obj;
        $ret = $obj->set_subject('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->subject());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_subject(null);
    }

    public function testSetMsgHtml()
    {
        $obj = $this->obj;
        $ret = $obj->set_msg_html('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->msg_html());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_msg_html(null);
    }

    public function testSetMsgText()
    {
        $obj = $this->obj;
        $ret = $obj->set_msg_txt('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->msg_txt());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_msg_txt(null);
    }

    public function testSetAttachments()
    {
        $obj = $this->obj;
        $ret = $obj->set_attachments(['foo']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['foo'], $obj->attachments());
    }

    public function testSetLog()
    {
        $obj = $this->obj;
        // $this->config()->set_default_log(false);
        // $this->assertNotTrue($obj->log());

        $ret = $obj->set_log(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->log());

        $obj->set_log(false);
        $this->assertNotTrue($obj->log());

    }

    public function testSetTrack()
    {
        $obj = $this->obj;
        // $this->config()->set_default_track(false);
        // $this->assertNotTrue($obj->track());

        $ret = $obj->set_track(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->track());

        $obj->set_track(false);
        $this->assertNotTrue($obj->track());

    }
}
