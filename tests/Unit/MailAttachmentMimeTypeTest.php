<?php

namespace Tests\Unit;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\helpdesk\Email\Emails;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\NullTransport;
use Tests\TestCase;

/**
 * The agent reply form stores the file extension ("pdf") in ticket_attachment.type and
 * that value reached Symfony Mime as the attachment's MIME type. Symfony splits it on
 * "/", so every reply with an attachment failed to email the customer with
 * "Undefined array key 1"; the job swallowed it into a one-line warning.
 */
class MailAttachmentMimeTypeTest extends TestCase
{
    public function test_extension_or_missing_type_becomes_a_real_mime_type()
    {
        $mailer = new PhpMailController();

        $this->assertSame('application/pdf', $mailer->attachmentMimeType('pdf', 'report.pdf'));
        $this->assertSame('image/png', $mailer->attachmentMimeType('PNG', 'shot.PNG'));
        $this->assertSame('image/jpeg', $mailer->attachmentMimeType('', 'photo.jpg'));
        $this->assertSame('image/png', $mailer->attachmentMimeType('image/png', 'x.bin'));
        // values that already worked are passed through exactly as before
        $this->assertSame('image/png; charset=binary', $mailer->attachmentMimeType('image/png; charset=binary', 'x.png'));
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $mailer->attachmentMimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'a.docx')
        );
        $this->assertSame('application/octet-stream', $mailer->attachmentMimeType('', 'no-extension'));
        $this->assertSame('application/octet-stream', $mailer->attachmentMimeType('zzzunknown', 'a.zzzunknown'));
    }

    public function test_reply_email_carries_every_attachment_with_a_valid_type()
    {
        Mail::setSymfonyTransport(new NullTransport());
        $sent = null;
        Event::listen(MessageSending::class, function ($event) use (&$sent) {
            $sent = $event->message;
        });

        $from = new Emails(['email_address' => 'support@example.com', 'email_name' => 'Support']);
        $attachments = [
            ['file_path' => base64_encode('%PDF-1.4'), 'file_name' => 'report.pdf', 'mime' => 'pdf', 'mode' => 'data'],
            ['file_path' => base64_encode('notes'), 'file_name' => 'notes.txt', 'mime' => 'text/plain', 'mode' => 'data'],
        ];

        (new PhpMailController())->laravelMail('customer@example.com', 'Customer', 'Re: issue', '<p>reply</p>', $from, null, $attachments);

        $this->assertNotNull($sent);
        $types = array_map(fn ($part) => $part->getFilename().' '.$part->getMediaType().'/'.$part->getMediaSubtype(), $sent->getAttachments());
        $this->assertSame(['report.pdf application/pdf', 'notes.txt text/plain'], $types);
    }
}
