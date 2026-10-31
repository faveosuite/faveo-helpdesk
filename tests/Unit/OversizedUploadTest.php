<?php

namespace Tests\Unit;

use App\Http\Controllers\Common\FileuploadController;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * PHP drops the whole body of a request over post_max_size, including the CSRF token, so a too
 * large attachment on "Submit a ticket" was reported as "Session expired or invalid".
 */
class OversizedUploadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_too_large_ticket_form_reports_the_size_not_an_expired_session()
    {
        $this->from('create-ticket')->call('POST', 'postedform', [], [], [], ['CONTENT_LENGTH' => (string) (200 * 1024 * 1024)])
            ->assertRedirect('create-ticket');

        $this->assertSame(trans('lang.attachment-too-large', ['size' => $this->maxSize()[1]]), session('fails'));
    }

    public function test_too_large_ajax_request_gets_413_json()
    {
        $this->call('POST', 'postedform', [], [], [], ['CONTENT_LENGTH' => (string) (200 * 1024 * 1024), 'HTTP_ACCEPT' => 'application/json'])
            ->assertStatus(413)
            ->assertJsonPath('result.fails', trans('lang.attachment-too-large', ['size' => $this->maxSize()[1]]));
    }

    public function test_ticket_with_small_attachment_is_still_created()
    {
        $subject = 'Upload test '.Str::random(8);
        $_SERVER['REQUEST_URI'] = '/postedform';

        $this->post('postedform', [
            'Name'       => 'Upload Tester', 'Email' => 'u'.Str::random(8).'@example.test', 'helptopic' => 1, 'priority' => 1,
            'Subject'    => $subject, 'Details' => 'Ticket body with a small attachment',
            'attachment' => [UploadedFile::fake()->createWithContent('notes.txt', 'A small attachment')],
        ]);

        $thread = Ticket_Thread::where('title', '=', $subject)->first();
        $this->assertNotNull($thread, 'ticket was not created: '.json_encode(session()->all()));
        $this->assertSame(1, Ticket_attachments::where('thread_id', '=', $thread->id)->count());
    }

    public function test_max_upload_size_is_post_max_size()
    {
        $helper = new FileuploadController();

        $this->assertEquals($helper->parse_size(ini_get('post_max_size')), $this->maxSize()[0]);
        $this->assertSame($helper->readableSize(ini_get('post_max_size')), $this->maxSize()[1]);
    }

    public function test_max_upload_size_is_shown_with_a_byte_unit()
    {
        $helper = new FileuploadController();

        $this->assertSame('8MB', $helper->readableSize('8M'));
        $this->assertSame('512KB', $helper->readableSize('512k'));
        $this->assertSame('1GB', $helper->readableSize('1G'));
        $this->assertSame('8MB', $helper->readableSize('8MB'));
        $this->assertSame('1048576', $helper->readableSize('1048576'));
        $this->assertMatchesRegularExpression('/^\d+(\.\d+)?[KMGT]B$|^\d+$/', $this->maxSize()[1]);
    }

    private function maxSize()
    {
        return (new FileuploadController())->file_upload_max_size();
    }
}
