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
use Fetch\Message;

/**
 * @package Fetch
 * @author  Robert Hafner <tedivm@tedivm.com>
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    public static function getMessage($id)
    {
        $server = ServerTest::getServer();

        return new \Fetch\Message($id, $server);
    }

    public function testConstructMessage()
    {
        $message = static::getMessage(3);
        $this->assertInstanceOf('\Fetch\Message', $message);
    }

    public function testGetOverview()
    {
        $message = static::getMessage(3);
        $overview = $message->getOverview();
        $this->assertEquals('Welcome', $overview->subject, 'Subject available from overview');
        $this->assertEquals('tedivm@tedivm.com', $overview->from, 'From available from overview');
        $this->assertEquals('testuser@tedivm.com', $overview->to, 'To available from overview');
        $this->assertEquals(1465, $overview->size, 'Size available from overview');
        $this->assertEquals(0, $overview->flagged, 'Flagged available from overview');
        $this->assertEquals(1, $overview->seen, 'Seen available from overview');
    }

    public function testGetHeaders()
    {
        $message = static::getMessage(3);
        $headers = $message->getHeaders();
        $this->assertEquals('Sun,  1 Dec 2013 21:14:03 -0800 (PST)', $headers->date, 'Headers contain the right date.');
        $this->assertEquals('testuser@tedivm.com', $headers->toaddress, 'Headers contain toaddress.');
        $this->assertEquals('tedivm@tedivm.com', $headers->fromaddress, 'Headers contain fromaddress');
    }

    public function testGetStructure()
    {

    }

    public function testGetMessageBody()
    {
        // easiest way to deal with php encoding issues is simply not to.
        $plaintextTest = 'f9377a89c9c935463a2b35c92dd61042';
        $convertedHtmlTest = '11498bcf191900d634ff8772a64ca523';
        $pureHtmlTest = '6a366ddecf080199284146d991d52169';

        $message = static::getMessage(3);
        $messageNonHTML = $message->getMessageBody();
        $this->assertEquals($plaintextTest, md5($messageNonHTML), 'Message returns as plaintext.');

        $messageHTML = $message->getMessageBody(true);
        $this->assertEquals($convertedHtmlTest, md5($messageHTML), 'Message converts from plaintext to HTML when requested.');

        $message = static::getMessage(4);
        $messageHTML = $message->getMessageBody(true);
        $this->assertEquals($pureHtmlTest, md5($messageHTML), 'Message returns as HTML.');

    }

    public function testGetPlainTextBody()
    {
        // easiest way to deal with php encoding issues is simply not to.
        $plaintextTest1 = 'f9377a89c9c935463a2b35c92dd61042';
        $plaintextTest2 = '0b8fc9b534a1789f1071f996f238a07a';
        $plaintextTest3 = 'd41d8cd98f00b204e9800998ecf8427e';

        $message = static::getMessage(3);
        $messagePlainText = $message->getPlainTextBody();
        $this->assertEquals($plaintextTest1, md5($messagePlainText), 'Message returns as plaintext.');

        $message = static::getMessage(4);
        $messagePlainText = $message->getPlainTextBody();
        $this->assertEquals($plaintextTest2, md5($messagePlainText), 'Message returns as plaintext.');

        $message = static::getMessage(6);
        $messagePlainText = $message->getPlainTextBody();
        $this->assertEquals($plaintextTest3, md5($messagePlainText), 'Message does not return as plaintext.');

    }

    public function testGetHtmlBody()
    {
        // easiest way to deal with php encoding issues is simply not to.
        $HtmlTest1 = 'd41d8cd98f00b204e9800998ecf8427e';
        $HtmlTest2 = '6a366ddecf080199284146d991d52169';

        $message = static::getMessage(3);
        $messageHtml = $message->getHtmlBody();
        $this->assertEquals($HtmlTest1, md5($messageHtml), 'Message does not return as HTML.');

        $message = static::getMessage(4);
        $messageHtml = $message->getHtmlBody();
        $this->assertEquals($HtmlTest2, md5($messageHtml), 'Message returns as HTML.');

    }

    public function testGetAddresses()
    {
        $message = static::getMessage(3);

        $addresses = $message->getAddresses('to');
        $this->assertEquals('testuser@tedivm.com', $addresses[0]['address'], 'Retrieving to user from address array.');

        $addressString = $message->getAddresses('to', true);
        $this->assertEquals('testuser@tedivm.com', $addressString, 'Returning To address as string.');

        $addresses = $message->getAddresses('from');
        $this->assertEquals('tedivm@tedivm.com', $addresses['address'], 'Returning From address as an address array.');

        $addressString = $message->getAddresses('from', true);
        $this->assertEquals('tedivm@tedivm.com', $addressString, 'Returning From address as string.');
    }

    public function testGetDate()
    {
        $message = static::getMessage(3);
        $this->assertEquals(1385961243, $message->getDate(), 'Returns date as timestamp.');
    }

    public function testGetSubject()
    {
        $message = static::getMessage(3);
        $this->assertEquals('Welcome', $message->getSubject(), 'Returns Subject.');
    }

    public function testDelete()
    {

    }

    public function testGetImapBox()
    {
        $server = ServerTest::getServer();
        $message = new \Fetch\Message('3', $server);
        $this->assertEquals($server, $message->getImapBox(), 'getImapBox returns Server used to create Message.');
    }

    public function testGetUid()
    {
        $message = static::getMessage('3');
        $this->assertEquals(3, $message->getUid(), 'Message returns UID');
    }

    public function testGetAttachments()
    {
        $messageWithoutAttachments = static::getMessage('3');
        $this->assertFalse($messageWithoutAttachments->getAttachments(), 'getAttachments returns false when no attachments present.');

        $messageWithAttachments = static::getMessage('6');
        $attachments = $messageWithAttachments->getAttachments();
        $this->assertCount(2, $attachments);
        foreach($attachments as $attachment)
            $this->assertInstanceOf('\Fetch\Attachment', $attachment, 'getAttachments returns Fetch\Attachment objects.');

        $attachment = $messageWithAttachments->getAttachments('Test_card.png.zip');
        $this->assertInstanceOf('\Fetch\Attachment', $attachment, 'getAttachment returns specified Fetch\Attachment object.');
    }

    public function testCheckFlag()
    {
        $message = static::getMessage('3');
        $this->assertFalse($message->checkFlag('flagged'));
        $this->assertTrue($message->checkFlag('seen'));
    }

    public function testSetFlag()
    {
        $message = static::getMessage('3');
        $this->assertFalse($message->checkFlag('answered'), 'Message is not answered.');

        $this->assertTrue($message->setFlag('answered'), 'setFlag returned true.');
        $this->assertTrue($message->checkFlag('answered'), 'Message was successfully answered.');

        $this->assertTrue($message->setFlag('answered', false), 'setFlag returned true.');
        $this->assertFalse($message->checkFlag('answered'), 'Message was successfully unanswered.');

        $message = static::getMessage('2');
        $this->assertFalse($message->checkFlag('flagged'), 'Message is not flagged.');

        $this->assertTrue($message->setFlag('flagged'), 'setFlag returned true.');
        $this->assertTrue($message->checkFlag('flagged'), 'Message was successfully flagged.');

        $message = static::getMessage('2');
        $this->assertTrue($message->setFlag('flagged', false), 'setFlag returned true.');
        $this->assertFalse($message->checkFlag('flagged'), 'Message was successfully unflagged.');
    }

    public function testMoveToMailbox()
    {
        $server = ServerTest::getServer();

        // Testing by moving message from "Test Folder" to "Sent"

        // Count Test Folder
        $testFolderNumStart = $server->numMessages('Test Folder');
        $server->setMailbox('Test Folder');
        $this->assertEquals($testFolderNumStart, $server->numMessages(), 'Server presents consistent information between numMessages when mailbox set and directly queried for number of messages');

        // Get message from Test Folder
        $message = current($server->getMessages(1));
        $this->assertInstanceOf('\Fetch\Message', $message, 'Server returned Message.');

        // Switch to Sent folder, count messages
        $sentFolderNumStart = $server->numMessages('Sent');
        $server->setMailbox('Sent');
        $this->assertEquals($sentFolderNumStart, $server->numMessages(), 'Server presents consistent information between numMessages when mailbox set and directly queried for number of messages');

        // Switch to "Flagged" folder in order to test that function properly returns to it
        $this->assertTrue($server->setMailBox('Flagged Email'));
        // Move the message!
        $this->assertTrue($message->moveToMailBox('Sent'));
        // Make sure we're still in the same folder
        $this->assertEquals('Flagged Email', $server->getMailBox(), 'Returned Server back to right mailbox.');
        $this->assertAttributeEquals('Sent', 'mailbox', $message, 'Message mailbox changed to new location.');
        // Make sure Test Folder lost a message
        $this->assertTrue($server->setMailBox('Test Folder'));
        $this->assertEquals($testFolderNumStart - 1, $server->numMessages(), 'Message moved out of Test Folder.');
        // Make sure Sent folder gains one
        $this->assertTrue($server->setMailBox('Sent'));
        $this->assertEquals($sentFolderNumStart + 1, $server->numMessages(), 'Message moved into Sent Folder.');
    }

    public function testDecode()
    {
        $quotedPrintableDecoded = "Now's the time for all folk to come to the aid of their country.";
        $quotedPrintable = <<<'ENCODE'
Now's the time =
for all folk to come=
 to the aid of their country.
ENCODE;
        $this->assertEquals($quotedPrintableDecoded, Message::decode($quotedPrintable, 'quoted-printable'), 'Decodes quoted printable');
        $this->assertEquals($quotedPrintableDecoded, Message::decode($quotedPrintable, 4), 'Decodes quoted printable');

        $testString = 'This is a test string';
        $base64 = base64_encode($testString);
        $this->assertEquals($testString, Message::decode($base64, 'base64'), 'Decodes quoted base64');
        $this->assertEquals($testString, Message::decode($base64, 3), 'Decodes quoted base64');

        $notEncoded = '> w - www.somesite.com.au<http://example.com/track/click.php?u=30204369&id=af4110cab28e464cb0702723bc84b3f3>';
        $this->assertEquals($notEncoded, Message::decode($notEncoded, 0), 'Nothing to decode');
    }

    public function testTypeIdToString()
    {
        $types = array();
        $types[0] = 'text';
        $types[1] = 'multipart';
        $types[2] = 'message';
        $types[3] = 'application';
        $types[4] = 'audio';
        $types[5] = 'image';
        $types[6] = 'video';
        $types[7] = 'other';
        $types[8] = 'other';
        $types[32] = 'other';

        foreach($types as $id => $type)
            $this->assertEquals($type, Message::typeIdToString($id));
    }

    public function testGetParametersFromStructure()
    {

    }

}
