<?php

namespace App\Http\Controllers\Agent\helpdesk;

// models
use App\Http\Controllers\Admin\MailFetch as Fetch;
use App\Http\Controllers\Controller;
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Settings\Ticket;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
// classes
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * MailController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class MailController extends Controller
{
    /**
     * constructor
     * Create a new controller instance.
     *
     * @param type TicketController $TicketController
     */
    public function __construct(TicketWorkflowController $TicketWorkflowController)
    {
        $this->middleware('board');
        $this->TicketWorkflowController = $TicketWorkflowController;
    }

    /**
     * Reademails.
     *
     * @return type
     */
    public function readmails(Emails $emails, Email $settings_email, System $system, Ticket $ticket)
    {
        //dd($emails);
        if ($settings_email->first()->email_fetching == 1) {
            if ($settings_email->first()->all_emails == 1) {
                $email = $emails->get();
                if ($email->count() > 0) {
                    foreach ($email as $e_mail) {
                        $this->fetch($e_mail);
                    }
                }
            }
        }
    }

    /**
     * separate reply.
     *
     * @param type $body
     *
     * @return type string
     */
    public function separate_reply($body)
    {
        $body2 = explode('---Reply above this line---', $body);
        $body3 = $body2[0];

        return $body3;
    }

    /**
     * @param object $email
     *
     * @return int
     */
    public function priority($email)
    {
        $priority = $email->priority;
        if (!$priority) {
            $priority = $this->ticketController()->getSystemDefaultPriority();
        }

        return $priority;
    }

    /**
     * get department.
     *
     * @param object $email
     *
     * @return int
     */
    public function department($email)
    {
        $department = $email->department;
        if (!$department) {
            $department = $this->ticketController()->getSystemDefaultDepartment();
        }

        return $department;
    }

    /**
     * get help topic.
     *
     * @param object $email
     *
     * @return int
     */
    public function helptopic($email)
    {
        //dd($email);
        $helptopic = $email->help_topic;
        if (!$helptopic) {
            $helptopic = $this->ticketController()->getSystemDefaultHelpTopic();
        }

        return $helptopic;
    }

    /**
     * get sla.
     *
     * @param object $email
     *
     * @return int
     */
    public function sla($email)
    {
        $helptopic = $this->helptopic($email);
        $help = Help_topic::where('id', '=', $helptopic)->first();
        if ($help) {
            $sla = $help->sla_plan;
        }
        if (!$sla) {
            $sla = $this->ticketController()->getSystemDefaultSla();
        }

        return $sla;
    }

    /**
     * get ticket controller.
     *
     * @return \App\Http\Controllers\Agent\helpdesk\TicketController
     */
    public function ticketController()
    {
        $PhpMailController = new \App\Http\Controllers\Common\PhpMailController();
        $NotificationController = new \App\Http\Controllers\Common\NotificationController();
        $controller = new TicketController($PhpMailController, $NotificationController);

        return $controller;
    }

    public function fetch($email)
    {
        //  dd($email);
        if ($email) {
            $username = $email->authUsername();
            $password = $email->password;
            $service = $email->fetching_protocol;
            $host = $email->fetching_host;
            $port = $email->fetching_port;
            $encryption = $email->fetching_encryption;
            $cert = $email->mailbox_protocol;
            $server = new Fetch($host, $port, $service);
            if ($encryption != null || $encryption != '') {
                $server->setFlag($encryption);
            }
            $server->setFlag($cert);
            $server->setAuthentication($username, $password);
            $date = date('d M Y', strtotime('-1 days'));
            $messages = $server->search("SINCE \"$date\" UNSEEN");
            $this->message($messages, $email);
        }
    }

    public function message($messages, $email)
    {
        if (count($messages) > 0) {
            foreach ($messages as $message) {
                $this->getMessageContent($message, $email);
            }
        }
    }

    public function getMessageContent($message, $email)
    {
        $body = $message->getMessageBody(true);
        if (!$body) {
            $body = $message->getMessageBody();
        }
        $body = $this->separateReply($body);
        $subject = $message->getSubject();
        $address = $message->getAddresses('reply-to');
        if (!$address) {
            $address = $message->getAddresses('from');
        }
        $collaborators = $this->collaburators($message, $email);
        $attachments = (!$message->getAttachments()) ? [] : $message->getAttachments();
        //dd(['body' => $body, 'subject' => $subject, 'address' => $address, 'cc' => $collaborator, 'attachments' => $attachments]);
        $this->workflow($address, $subject, $body, $collaborators, $attachments, $email);
    }

    public function workflow($address, $subject, $body, $collaborator, $attachments, $email)
    {
        $fromaddress = checkArray('address', $address[0]);
        $fromname = checkArray('name', $address[0]);
        $helptopic = $this->helptopic($email);
        $sla = $this->sla($email);
        $priority = $this->priority($email);
        $ticket_source = Ticket_source::where('name', '=', 'email')->first();
        $source = $ticket_source->id;
        $dept = $this->department($email);
        $get_helptopic = Help_topic::where('id', '=', $helptopic)->first();
        $assign = $get_helptopic->auto_assign;
        $form_data = null;
        $team_assign = null;
        $ticket_status = null;
        $auto_response = $email->auto_response;
        $result = $this->TicketWorkflowController->workflow($fromaddress, $fromname, $subject, $body, $phone = '', $phonecode = '', $mobile_number = '', $helptopic, $sla, $priority, $source, $collaborator, $dept, $assign, $team_assign, $ticket_status, $form_data = [], $auto_response);
        if ($result[1] == true) {
            $this->updateThread($result[0], $body, $attachments);
        }
    }

    public function updateThread($ticket_number, $body, $attachments)
    {
        $ticket_table = Tickets::where('ticket_number', '=', $ticket_number)->first();
        $thread_id = Ticket_Thread::where('ticket_id', '=', $ticket_table->id)->max('id');
        $thread = Ticket_Thread::where('id', '=', $thread_id)->first();
        $thread->body = $this->separate_reply($body);
        $thread->save();
        if (file_exists(app_path('/FaveoStorage/Controllers/StorageController.php'))) {
            try {
                $storage = new \App\FaveoStorage\Controllers\StorageController();
                $storage->saveAttachments($thread->id, $attachments);
            } catch (\Exception $ex) {
                loging('attachment', $ex->getMessage());
            }
        } else {
            loging('attachment', 'FaveoStorage not installed');
        }

        \Log::info('Ticket has created : ', ['id' => $thread->ticket_id]);
    }

    public function saveAttachments($thread_id, $attachments = [])
    {
        if (is_array($attachments) && count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $structure = $attachment->getStructure();
                $disposition = 'ATTACHMENT';
                if (isset($structure->disposition)) {
                    $disposition = $structure->disposition;
                }

                $filename = Str::random(16).'-'.$attachment->getFileName();
                $type = $attachment->getMimeType();
                $size = $attachment->getSize();
                $data = $attachment->getData();
                //$path = storage_path('/');
                //$attachment->saveToDirectory($path);
                $this->manageAttachment($data, $filename, $type, $size, $disposition, $thread_id);
                $this->updateBody($attachment, $thread_id, $filename);
            }
        }
    }

    public function manageAttachment($data, $filename, $type, $size, $disposition, $thread_id)
    {
        $upload = new Ticket_attachments();
        $upload->file = $data;
        $upload->thread_id = $thread_id;
        $upload->name = $filename;
        $upload->type = $type;
        $upload->size = $size;
        $upload->poster = $disposition;
        if ($data && $size && $disposition) {
            $upload->save();
        }
    }

    public function updateBody($attachment, $thread_id, $filename)
    {
        $structure = $attachment->getStructure();
        $disposition = 'ATTACHMENT';
        if (isset($structure->disposition)) {
            $disposition = $structure->disposition;
        }
        if ($disposition == 'INLINE' || $disposition == 'inline') {
            $id = str_replace('>', '', str_replace('<', '', $structure->id));
//            $filename = $attachment->getFileName();
            $threads = new Ticket_Thread();
            $thread = $threads->find($thread_id);
            $body = $thread->body;
            $body = str_replace('cid:'.$id, $filename, $body);
            $thread->body = $body;
            $thread->save();
        }
    }

    public function collaburators($message, $email)
    {
        $this_address = $email->email_address;
        $collaborator_cc = $message->getAddresses('cc');
        //dd($collaborator_cc);
        $collaborator_bcc = $message->getAddresses('bcc');
        $collaborator_to = $message->getAddresses('to');
        $cc_array = [];
        $bcc_array = [];
        $to_array = [];
        if ($collaborator_cc) {
            foreach ($collaborator_cc as $cc) {
                $name = checkArray('name', $cc);
                $address = checkArray('address', $cc);
                $cc_array[$address] = $name;
            }
        }
        if ($collaborator_bcc) {
            foreach ($collaborator_bcc as $bcc) {
                $name = checkArray('name', $bcc);
                $address = checkArray('address', $bcc);
                $bcc_array[$address] = $name;
            }
        }
        if ($collaborator_to) {
            foreach ($collaborator_to as $to) {
                $name = checkArray('name', $to);
                $address = checkArray('address', $to);
                $to_array[$address] = $name;
            }
        }
        $array = array_merge($bcc_array, $cc_array);
        $array = array_merge($array, $to_array);
        if (array_key_exists($this_address, $array)) {
            unset($array[$this_address]);
        }

        return $array;
    }

    /**
     * function to load data.
     *
     * @param type $id
     *
     * @return type file
     */
    public function get_data(Request $request)
    {
        $id = $request->input('image_id');
        $attachment = Ticket_attachments::where('id', '=', $id)->first();

        if (!$attachment) {
            abort(404);
        }

        // Resolve ticket via thread → ticket chain
        $thread = Ticket_Thread::where('id', '=', $attachment->thread_id)->first();
        $ticket = $thread ? Tickets::where('id', '=', $thread->ticket_id)->first() : null;

        if (!$ticket) {
            abort(404);
        }

        // Authorization: verify the requesting user may access this ticket
        $user = \Auth::user();
        if ($user->role === 'user') {
            if ($ticket->user_id != $user->id) {
                abort(403);
            }
        } elseif ($user->role === 'agent') {
            if ($ticket->dept_id != $user->primary_dpt && $ticket->assigned_to != $user->id) {
                abort(403);
            }
        }
        // admin: no restriction

        if (mime($attachment->type) == true) {
            echo "<img src=data:$attachment->type;base64,".$attachment->file.'>';
        } else {
            $file = base64_decode($attachment->file);

            return response($file)
                ->header('Cache-Control', 'no-cache private')
                ->header('Content-Description', 'File Transfer')
                ->header('Content-Type', $attachment->type)
                ->header('Content-length', strlen($file))
                ->header('Content-Disposition', 'attachment; filename='.$attachment->name)
                ->header('Content-Transfer-Encoding', 'binary');
        }
    }

    /**
     * separate reply.
     *
     * @param type $body
     *
     * @return type string
     */
    public function separateReply($body)
    {
        $body2 = explode('---Reply above this line---', $body);
        if (is_array($body2) && array_key_exists(0, $body2)) {
            $body = $body2[0];
        }

        return $body;
    }
}
