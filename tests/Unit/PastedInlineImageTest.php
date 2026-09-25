<?php

namespace Tests\Unit;

use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

/**
 * The editor puts a pasted/dropped image in the body as a data: URI. The XSS
 * sanitizer turned that src into "#", so the image was lost (broken image on
 * the ticket page), and the raw base64 in the mail pushed it past Gmail's clip
 * limit, hiding the ticket link (#8240).
 */
class PastedInlineImageTest extends TestCase
{
    use DatabaseTransactions;

    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function png(): string
    {
        $image = imagecreatetruecolor(20, 10);
        ob_start();
        imagepng($image);

        return ob_get_clean();
    }

    public function test_pasted_image_on_a_new_ticket_is_kept_as_an_inline_attachment()
    {
        $png = $this->png();
        $subject = 'Pasted image '.Str::random(8);
        $_SERVER['REQUEST_URI'] = '/postedform';

        $this->post('postedform', [
            'Name'      => 'Image Tester', 'Email' => 'i'.Str::random(8).'@example.test', 'helptopic' => 1, 'priority' => 1,
            'Subject'   => $subject,
            'Details'   => '<p>see screenshot</p><p><img src="data:image/png;base64,'.base64_encode($png).'" data-filename="shot.png"></p>',
        ]);

        $thread = Ticket_Thread::where('title', '=', $subject)->first();
        $this->assertNotNull($thread, 'ticket was not created: '.json_encode(session()->all()));

        $inline = Ticket_attachments::where('thread_id', '=', $thread->id)->where('poster', '=', 'INLINE')->get();
        foreach ($inline as $attachment) {
            $this->files[] = $attachment->path.DIRECTORY_SEPARATOR.$attachment->name;
        }
        $this->assertCount(1, $inline);
        $this->assertSame('image/png', $inline[0]->type);

        $stored = $thread->getAttributes()['body'];
        $this->assertStringNotContainsString('data:image', $stored);
        $this->assertStringNotContainsString('src="#"', $stored);
        $this->assertStringContainsString('src="'.$inline[0]->name.'"', $stored);

        // shown again on the ticket page
        $this->assertStringContainsString('data:image/png;base64,'.base64_encode($png), $thread->fresh()->purify());
    }

    public function test_pasted_image_in_an_agent_reply_shows_on_the_agent_ticket_page()
    {
        $png = $this->png();
        $subject = 'Agent reply image '.Str::random(8);
        $_SERVER['REQUEST_URI'] = '/postedform';
        $this->post('postedform', [
            'Name'    => 'Image Tester', 'Email' => 'i'.Str::random(8).'@example.test', 'helptopic' => 1, 'priority' => 1,
            'Subject' => $subject, 'Details' => 'Ticket body without an image',
        ]);
        $first = Ticket_Thread::where('title', '=', $subject)->first();
        $this->assertNotNull($first, 'ticket was not created: '.json_encode(session()->all()));

        // agent replies are printed raw in the timeline, not through purify() like client posts
        $reply = new Ticket_Thread();
        $reply->ticket_id = $first->ticket_id;
        $reply->user_id = 1;
        $reply->poster = 'support';
        $reply->is_internal = 0;
        $reply->body = '<p>see screenshot</p><p><img src="data:image/png;base64,'.base64_encode($png).'"></p>';
        $reply->save();
        $inline = Ticket_attachments::where('thread_id', '=', $reply->id)->firstOrFail();
        $this->files[] = $inline->path.DIRECTORY_SEPARATOR.$inline->name;

        $admin = \App\User::where('role', '=', 'admin')->firstOrFail();
        $page = $this->actingAs($admin)->get('thread/'.$first->ticket_id)->assertOk()->getContent();

        $this->assertStringContainsString('data:image/png;base64,'.base64_encode($png), $page);
        $this->assertStringNotContainsString('src="'.$inline->name.'"', $page);
    }

    public function test_only_real_raster_images_are_taken_out()
    {
        $svg = '<img src="data:image/svg+xml;base64,'.base64_encode('<svg onload="alert(1)"/>').'">';
        $fake = '<img src="data:image/png;base64,'.base64_encode('not an image').'">';

        $this->assertSame($svg.$fake, replaceDataUriImages($svg.$fake, fn () => 'replaced.png'));
    }

    public function test_mail_sends_pasted_images_as_embedded_parts()
    {
        $png = $this->png();
        $message = new Message(new Email());
        $html = view('emails.mail', [
            'data'    => '<img src="data:image/png;base64,'.base64_encode($png).'"> check_ticket/abc',
            'message' => $message,
        ])->render();

        $this->assertStringNotContainsString('data:image', $html);
        $this->assertMatchesRegularExpression('/src="cid:[^"]+"/', $html);
        $this->assertCount(1, $message->getSymfonyMessage()->getAttachments());
    }
}
