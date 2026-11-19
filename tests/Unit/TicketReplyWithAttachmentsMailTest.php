<?php

namespace Tests\Unit;

use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Transport\NullTransport;
use Tests\TestCase;

/**
 * End to end: an agent replies through the reply form with attachments and the
 * customer email is really built (sync queue, SendEmail job, PhpMailController,
 * Symfony Mime). Only the transport is replaced, by one that sends nothing.
 *
 * Before the fix the job failed with "Undefined array key 1" (the stored file
 * extension reached Symfony as the MIME type), logged a bare warning and flashed it
 * as the "Alert!" on the ticket page; and only the first file was ever attached.
 */
class TicketReplyWithAttachmentsMailTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $role, array $overrides = []): User
    {
        $suffix = Str::random(10);
        $user = new User(array_merge([
            'first_name'   => 'Reply',
            'last_name'    => $suffix,
            'email'        => 'reply-'.$suffix.'@example.test',
            'user_name'    => 'reply_'.$suffix,
            'password'     => Hash::make(Str::random(12)),
            'active'       => 1,
            'role'         => $role,
            'agent_tzone'  => 81,
            'assign_group' => 1,
            'primary_dpt'  => 1,
        ], $overrides));
        $user->save();

        return $user;
    }

    public function test_reply_with_attachments_emails_the_customer_with_every_file()
    {
        // A sending account for the ticket's department whose driver setup leaves the
        // mailer's transport alone ('mailgun' is a no-op in setMailConfig), so the
        // NullTransport below is what actually "sends".
        $account = new Emails([
            'email_address'    => 'support-'.Str::random(6).'@example.test',
            'email_name'       => 'Support',
            'department'       => 1,
            'sending_status'   => 1,
            'sending_protocol' => 'mailgun',
        ]);
        $account->save();
        \DB::table('queue_services')->update(['status' => 0]);
        \DB::table('queue_services')->where('short_name', 'sync')->update(['status' => 1]);

        Mail::setSymfonyTransport(new NullTransport());
        $sent = [];
        Event::listen(MessageSending::class, function ($event) use (&$sent) {
            $sent[] = $event->message;
        });
        Log::spy();

        $customer = $this->makeUser('user', ['primary_dpt' => null, 'assign_group' => null]);
        $ticket = new Tickets([
            'ticket_number' => 'MAIL-'.random_int(100000, 999999),
            'user_id'       => $customer->id,
            'priority_id'   => 2,
            'sla'           => 2,
            'help_topic_id' => 1,
            'status'        => 1,
            'source'        => 1,
            'is_deleted'    => 0,
        ]);
        $ticket->save();
        $ticket->dept_id = 1;
        $ticket->save();
        (new Ticket_Thread(['ticket_id' => $ticket->id, 'user_id' => $customer->id, 'poster' => 'client', 'title' => 'Network issue', 'body' => 'help']))->save();

        $this->actingAs($this->makeUser('admin'));

        $response = $this->post(route('ticket.reply', ['id' => $ticket->id]), [
            'ticket_ID'     => $ticket->id,
            'reply_content' => '<p>Please see the attached files.</p>',
            'attachment'    => [
                UploadedFile::fake()->createWithContent('report.pdf', '%PDF-1.4 test'),
                UploadedFile::fake()->createWithContent('notes.txt', 'some notes'),
            ],
        ]);

        // the reply itself
        $response->assertOk()->assertJsonPath('result.success', trans('lang.you_have_successfully_replied_to_your_ticket'));
        $reply = Ticket_Thread::where('ticket_id', $ticket->id)->where('poster', 'support')->latest('id')->first();
        $this->assertNotNull($reply);

        // stored attachment rows are unchanged: still the extension, as the ticket page expects
        $this->assertEqualsCanonicalizing(['pdf', 'txt'], Ticket_attachments::where('thread_id', $reply->id)->pluck('type')->all());

        // the customer email was built and carries both files with valid MIME types
        $toCustomer = collect($sent)->first(fn ($m) => collect($m->getTo())->contains(fn ($a) => $a->getAddress() === $customer->email));
        $this->assertNotNull($toCustomer, 'no email was sent to the customer');
        $files = array_map(fn ($p) => $p->getFilename().' '.$p->getMediaType().'/'.$p->getMediaSubtype(), $toCustomer->getAttachments());
        $this->assertEqualsCanonicalizing(['report.pdf application/pdf', 'notes.txt text/plain'], $files);

        // no "Alert!" on the next page load and nothing written to the log
        $this->assertFalse(session()->has('fails'), 'mail failure flashed: '.session('fails'));
        Log::shouldNotHaveReceived('warning');
        Log::shouldNotHaveReceived('error');
    }
}
