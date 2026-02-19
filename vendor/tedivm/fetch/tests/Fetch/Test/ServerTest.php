<?php

/*
 * This file is part of the Fetch library.
 *
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fetch\Test;

use Fetch\Server;

/**
 * @package Fetch
 * @author  Robert Hafner <tedivm@tedivm.com>
 */
class ServerTest extends \PHPUnit_Framework_TestCase
{
    public static $num_messages_inbox = 12;

    /**
     * @dataProvider flagsDataProvider
     * @param string  $expected server string with %host% placeholder
     * @param integer $port     to use (needed to test behavior on port 143 and 993 from constructor)
     * @param array   $flags    to set/unset ($flag => $value)
     */
    public function testFlags($expected, $port, $flags)
    {
        $server = new Server(TESTING_SERVER_HOST, $port);

        foreach ($flags as $flag => $value) {
            $server->setFlag($flag, $value);
        }

        $this->assertEquals(str_replace('%host%', TESTING_SERVER_HOST, $expected), $server->getServerString());
    }

    public function testFlagOverwrite()
    {
        $server = static::getServer();

        $server->setFlag('TestFlag', 'true');
        $this->assertAttributeContains('TestFlag=true', 'flags', $server);

        $server->setFlag('TestFlag', 'false');
        $this->assertAttributeContains('TestFlag=false', 'flags', $server);
    }

    public function flagsDataProvider()
    {
        return array(
                array('{%host%:143/novalidate-cert}', 143, array()),
                array('{%host%:143/validate-cert}', 143, array('validate-cert' => true)),
                array('{%host%:143}', 143, array('novalidate-cert' => false)),
                array('{%host%:993/ssl}', 993, array()),
                array('{%host%:993}', 993, array('ssl' => false)),
                array('{%host%:100/tls}', 100, array('tls' => true)),
                array('{%host%:100/tls}', 100, array('tls' => true, 'tls' => true)),
                array('{%host%:100/notls}', 100, array('tls' => true, 'notls' => true)),
                array('{%host%:100}', 100, array('ssl' => true, 'ssl' => false)),
                array('{%host%:100/user=foo}', 100, array('user' => 'foo')),
                array('{%host%:100/user=foo}', 100, array('user' => 'foo', 'user' => 'foo')),
                array('{%host%:100/user=bar}', 100, array('user' => 'foo', 'user' => 'bar')),
                array('{%host%:100}', 100, array('user' => 'foo', 'user' => false)),
        );
    }

    /**
     * @dataProvider connectionDataProvider
     * @param integer $port    to use (needed to test behavior on port 143 and 993 from constructor)
     * @param array   $flags   to set/unset ($flag => $value)
     * @param string  $message Assertion message
     */
    public function testConnection($port, $flags, $message)
    {
        $server = new Server(TESTING_SERVER_HOST, $port);
        $server->setAuthentication(TEST_USER, TEST_PASSWORD);

        foreach ($flags as $flag => $value) {
            $server->setFlag($flag, $value);
        }

        $imapSteam = $server->getImapStream();
        $this->assertInternalType('resource', $imapSteam, $message);
    }

    public function connectionDataProvider()
    {
        return array(
            array(143, array(), 'Connects with default settings.'),
            array(993, array('novalidate-cert' => true), 'Connects over SSL (self signed).'),
        );
    }

    public function testNumMessages()
    {
        $server = static::getServer();
        $numMessages = $server->numMessages();
        $this->assertEquals(self::$num_messages_inbox, $numMessages);
        $this->assertEquals(0, $server->numMessages( 'DOESNOTEXIST'.time() ) );
    }

    public function testGetMessages()
    {
        $server = static::getServer();
        $messages = $server->getMessages(5);

        $this->assertCount(5, $messages, 'Five messages returned');
        foreach ($messages as $message) {
            $this->assertInstanceOf('\Fetch\Message', $message, 'Returned values are Messages');
        }
    }

    public function testGetMessagesOrderedByDateAsc()
    {
        $server = static::getServer();
        $messages = $server->getOrderedMessages(SORTDATE, false, 2);

        $this->assertCount(2, $messages, 'Two messages returned');
        $this->assertGreaterThan($messages[0]->getDate(), $messages[1]->getDate(), 'Messages in ascending order');
    }

    public function testGetMessagesOrderedByDateDesc()
    {
        $server = static::getServer();
        $messages = $server->getOrderedMessages(SORTDATE, true, 2);

        $this->assertCount(2, $messages, 'Two messages returned');
        $this->assertLessThan($messages[0]->getDate(), $messages[1]->getDate(), 'Messages in descending order');
    }

    public function testGetMailBox()
    {
        $server = static::getServer();
        $this->assertEquals('', $server->getMailBox());
        $this->assertTrue($server->setMailBox('Sent'));
        $this->assertEquals('Sent', $server->getMailBox());
    }

    public function testSetMailBox()
    {
        $server = static::getServer();

        $this->assertTrue($server->setMailBox('Sent'));
        $this->assertEquals('Sent', $server->getMailBox());

        $this->assertTrue($server->setMailBox('Flagged Email'));
        $this->assertEquals('Flagged Email', $server->getMailBox());

        $this->assertFalse($server->setMailBox('Cheese'));

        $this->assertTrue($server->setMailBox(''));
        $this->assertEquals('', $server->getMailBox());
    }

    public function testHasMailBox()
    {
        $server = static::getServer();

        $this->assertTrue($server->hasMailBox('Sent'), 'Has mailbox "Sent"');
        $this->assertTrue($server->hasMailBox('Flagged Email'), 'Has mailbox "Flagged Email"');
        $this->assertFalse($server->hasMailBox('Cheese'), 'Does not have mailbox "Cheese"');
    }

    public function testListMailBoxes()
    {
        $server = static::getServer();
        $spec = sprintf('{%s:143/novalidate-cert}', TESTING_SERVER_HOST);

        $list = $server->listMailboxes('*');
        $this->assertContains($spec.'Sent', $list, 'Has mailbox "Sent"');
        $this->assertNotContains($spec.'Cheese', $list, 'Does not have mailbox "Cheese"');
    }

    public function testCreateMailbox()
    {
        $server = static::getServer();

        $this->assertFalse($server->hasMailBox('Cheese'), 'Does not have mailbox "Cheese"');
        $this->assertTrue($server->createMailBox('Cheese'), 'createMailbox returns true.');
        $this->assertTrue($server->hasMailBox('Cheese'), 'Mailbox "Cheese" was created');
    }

    public function testDeleteMailbox()
    {
        $server = static::getServer();
        $this->assertTrue($server->hasMailBox('Cheese'), 'Does have mailbox "Cheese"');
        $this->assertTrue($server->deleteMailBox('Cheese'), 'deleteMailBox returns true.');
        $this->assertFalse($server->hasMailBox('Cheese'), 'Mailbox "Cheese" was deleted');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetOptionsException()
    {
        $server = static::getServer();
        $server->setOptions('purple');
    }

    public function testSetOptions()
    {
        $server = static::getServer();
        $server->setOptions(5);
        $this->assertAttributeEquals(5, 'options', $server);
    }

    public function testExpunge()
    {
        $server = static::getServer();
        $message = $server->getMessageByUid(12);

        $this->assertInstanceOf('\Fetch\Message', $message, 'Message exists');

        $message->delete();

        $this->assertInstanceOf('\Fetch\Message', $server->getMessageByUid(12), 'Message still present after being deleted but before being expunged.');

        $server->expunge();

        $this->assertFalse($server->getMessageByUid(12), 'Message successfully expunged');
    }

    public static function getServer()
    {
        $server = new Server(TESTING_SERVER_HOST, 143);
        $server->setAuthentication(TEST_USER, TEST_PASSWORD);

        return $server;
    }
}
