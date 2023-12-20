<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\TemplateType;
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Settings\Email;
use App\User;
use Auth;
use Exception;
use Lang;
use Mail;

class PhpMailController extends Controller
{
    /**
     * @var variable to instantiate common mailer class
     */
    public function __construct()
    {
        $this->commonMailer = new CommonMailer();
    }

    public function fetch_smtp_details($id)
    {
        $emails = Emails::where(
            [['id', '=', $id],
                ['sending_status', '=', 1], ]
        )
        ->first();

        return $emails;
    }

    /**
     * Fetching comapny name to send mail.
     *
     * @return type
     */
    public function company()
    {
        $company = Company::Where('id', '=', '1')->first();
        if ($company->company_name == null) {
            $company = 'Support Center';
        } else {
            $company = $company->company_name;
        }

        return $company;
    }

    /**
     * Function to choose from address.
     *
     * @param type Reg        $reg
     * @param type Department $dept_id
     *
     * @return type integer
     */
    public function mailfrom($reg, $dept_id)
    {
        $email_id = '';
        $emails = Emails::where('department', '=', $dept_id)->first();

        $email = Email::find(1);
        if ($emails && $emails->sending_status) {
            $email_id = $emails->id;
        } else {
            $email_id = $email->sys_email;
        }

        return $email_id;
    }

    public function sendmail($from, $to, $message, $template_variables)
    {
        $this->setQueue();
        $job = new \App\Jobs\SendEmail($from, $to, $message, $template_variables);
        $this->dispatch($job);
    }

    public function sendEmail($from, $to, $message, $template_variables = [])
    {
        $from_address = $this->fetch_smtp_details($from);
        if ($from_address == null) {
            throw new Exception(Lang::get('lang.system-email-not-configured'));
        }

        $this->setMailConfig($from_address);

        $recipants = $this->checkElement('email', $to);
        $recipantname = $this->checkElement('name', $to);
        $cc = $this->checkElement('cc', $to);
        $bc = $this->checkElement('bc', $to);
        $subject = $this->checkElement('subject', $message);
        $content = $this->checkElement('body', $message);
        $template_type = $this->checkElement('scenario', $message);
        $attachment = $this->checkElement('attachments', $message);

        $agent = null;
        // template variables
        if (Auth::user()) {
            $agent = Auth::user()->user_name;
        }
        $ticket_agent_name = $this->checkElement('ticket_agent_name', $template_variables);
        $ticket_number = $this->checkElement('ticket_number', $template_variables);
        $ticket_client_name = $this->checkElement('ticket_client_name', $template_variables);
        $ticket_client_email = $this->checkElement('ticket_client_email', $template_variables);
        $ticket_body = $this->checkElement('ticket_body', $template_variables);
        $ticket_assigner = $this->checkElement('ticket_assigner', $template_variables);
        $ticket_link_with_number = $this->checkElement('ticket_link_with_number', $template_variables);

        $system_from = $this->checkElement('system_from', $template_variables);
        if ($system_from === '') {
            $system_from = $this->company();
        }
        $system_link = $this->checkElement('system_link', $template_variables);
        if ($system_link === '') {
            $system_link = \Config::get('app.url');
        }
        $ticket_link = $this->checkElement('ticket_link', $template_variables);
        $system_error = $this->checkElement('system_error', $template_variables);
        $agent_sign = $this->checkElement('agent_sign', $template_variables);
        $department_sign = $this->checkElement('department_sign', $template_variables);
        $password_reset_link = $this->checkElement('password_reset_link', $template_variables);
        $user_password = $this->checkElement('user_password', $template_variables);
        $merged_ticket_numbers = $this->checkElement('merged_ticket_numbers', $template_variables);
        $email_address = $this->checkElement('email_address', $template_variables);
        $user = $this->checkElement('user', $template_variables);

        $status = \DB::table('settings_email')->first();

        $template = TemplateType::where('name', '=', $template_type)->first();

        $set = \App\Model\Common\TemplateSet::where('name', '=', $status->template)->first();
        if ($template) {
            if (isset($set['id'])) {
                $template_data = \App\Model\Common\Template::where('set_id', '=', $set->id)->where('type', '=', $template->id)->first();
                $contents = $template_data->message;
                if ($template_data->variable == 1) {
                    if ($template_data->subject) {
                        $subject = $template_data->subject;
                        if ($ticket_number != null) {
                            $subject = $subject.' [#'.$ticket_number.']';
                        }
                    } else {
                        $subject = $message['subject'];
                    }
                } else {
                    $subject = $message['subject'];
                }
            } else {
                $contents = null;
                $subject = null;
            }

            $variables = ['{!!$user!!}', '{!!$agent!!}', '{!!$ticket_number!!}', '{!!$content!!}', '{!!$from!!}', '{!!$ticket_agent_name!!}', '{!!$ticket_client_name!!}', '{!!$ticket_client_email!!}', '{!!$ticket_body!!}', '{!!$ticket_assigner!!}', '{!!$ticket_link_with_number!!}', '{!!$system_error!!}', '{!!$agent_sign!!}', '{!!$department_sign!!}', '{!!$password_reset_link!!}', '{!!$email_address!!}', '{!!$user_password!!}', '{!!$system_from!!}', '{!!$system_link!!}', '{!!$ticket_link!!}', '{!!$merged_ticket_numbers!!}'];

            $data = [$user, $agent, $ticket_number, $content, $from, $ticket_agent_name, $ticket_client_name, $ticket_client_email, $ticket_body, $ticket_assigner, $ticket_link_with_number, $system_error, $agent_sign, $department_sign, $password_reset_link, $email_address, $user_password, $system_from, $system_link, $ticket_link, $merged_ticket_numbers];

            foreach ($variables as $key => $variable) {
                $messagebody = str_replace($variables[$key], $data[$key], $contents);
                $contents = $messagebody;
            }

            if ($template_type == 'ticket-reply-agent') {
                $line = '---Reply above this line--- <br/><br/>';
                $content = $line.$messagebody;
            } else {
                $content = $messagebody;
            }
        }
        $send = $this->laravelMail($recipants, $recipantname, $subject, $content, $from_address, $cc, $attachment);

        return $send;
    }

    public function setMailConfig($mail)
    {
        switch ($mail->sending_protocol) {
            case 'smtp':
                $config = ['host' => $mail->sending_host,
                    'port'        => $mail->sending_port,
                    'security'    => $mail->sending_encryption,
                    'username'    => $mail->authUsername(),
                    'password'    => $mail->password,
                ];
                if (!$this->commonMailer->setSmtpDriver($config)) {
                    \Log::info('Invaid configuration :- '.$config);

                    return 'invalid mail configuration';
                }
                break;
            case 'send_mail':
                $config = [
                    'host'     => \Config::get('mail.host'),
                    'port'     => \Config::get('mail.port'),
                    'security' => \Config::get('mail.encryption'),
                    'username' => \Config::get('mail.username'),
                    'password' => \Config::get('mail.password'),
                ];
                $this->commonMailer->setSmtpDriver($config);
                break;
            case 'mailgun':
                $this->commonMailer->setMailGunDriver(null);
                break;
            default:
                return 'Mail driver not supported';
        }
    }

    public function setServices($emailid, $protocol)
    {
        $service = new \App\Model\MailJob\FaveoMail();
        $services = $service->where('email_id', $emailid)->pluck('value', 'key')->toArray();
        $controller = new \App\Http\Controllers\Admin\helpdesk\EmailsController();
        $controller->setServiceConfig($protocol, $services);
    }

    public function checkElement($element, $array)
    {
        $value = '';
        if (is_array($array)) {
            if (array_key_exists($element, $array)) {
                $value = $array[$element];
            }
        }

        return $value;
    }

    public function laravelMail($to, $toname, $subject, $data, $from_address, $cc, $attach)
    {
        //dd($to, $toname, $subject, $data, $cc, $attach);
        //dd(\Config::get('mail'));
        //dd($attach);

        $mail = Mail::send('emails.mail', ['data' => $data], function ($m) use ($to, $subject, $toname, $cc, $attach, $from_address) {
            $m->to($to, $toname)->subject($subject);
            $m->from($from_address->email_address, $from_address->email_name);
            if ($cc != null) {
                foreach ($cc as $collaborator) {
                    //mail to collaborators
                    $collab_user_id = $collaborator->user_id;
                    $user_id_collab = User::where('id', '=', $collab_user_id)->first();
                    $collab_email = $user_id_collab->email;
                    $m->cc($collab_email);
                }
            }

            //            $mail->addBCC($bc);
            $size = ($attach) ? count($attach) : 0;
            if ($size > 0) {
                for ($i = 0; $i < $size; $i++) {
                    if (is_array($attach) && array_key_exists($i, $attach)) {
                        $mode = 'normal';
                        if (is_array($attach[$i]) && array_key_exists('mode', $attach[$i])) {
                            $mode = $attach[$i]['mode'];
                        }
                        $file = $attach[$i]['file_path'];
                        $name = $attach[$i]['file_name'];
                        $mime = $attach[$i]['mime'];
                        $this->attachmentMode($m, $file, $name, $mime, $mode);
                        break;
                    }
                }
            }
        });
        if (is_object($mail) || (is_object($mail) && $mail->getStatusCode() == 200)) {
            $mail = 1;
        }

        return $mail;
    }

    public function setQueue()
    {
        $short = 'database';
        $field = [
            'driver' => 'database',
            'table'  => 'jobs',
            'queue'  => 'default',
            'expire' => 60,
        ];
        $queue = new \App\Model\MailJob\QueueService();
        $active_queue = $queue->where('status', 1)->first();
        if ($active_queue) {
            $short = $active_queue->short_name;
            $fields = new \App\Model\MailJob\FaveoQueue();
            $field = $fields->where('service_id', $active_queue->id)->pluck('value', 'key')->toArray();
        }
        $this->setQueueConfig($short, $field);
    }

    public function setQueueConfig($short, $field)
    {
        \Config::set('queue.default', $short);
        foreach ($field as $key => $value) {
            \Config::set("queue.connections.$short.$key", $value);
        }
    }

    public function attachmentMode($message, $file, $name, $mime, $mode)
    {
        if ($mode == 'data') {
            return $message->attachData(base64_decode($file, true), $name, ['mime' => $mime]);
        }

        return $message->attach($file, ['as' => $name, 'mime' => $mime]);
    }
}
