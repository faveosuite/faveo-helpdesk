<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controllers
use App\Http\Controllers\Admin\MailFetch as Fetch;
use App\Http\Controllers\Controller;
use App\Http\Requests\helpdesk\EmailsRequest;
use App\Http\Requests\helpdesk\Mail\MailRequest;
// model
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Utility\MailboxProtocol;
// classes
use Crypt;
use Exception;
use Lang;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Yajra\DataTables\Facades\DataTables;

/**
 * ======================================
 * EmailsController.
 * ======================================
 * This Controller is used to define below mentioned set of functions applied to the Emails in the system.
 *
 * @author Ladybird <info@ladybirdweb.com>
 */
class EmailsController extends Controller
{
    /**
     * Defining constructor variables.
     *
     * @return type
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('roles');
    }

    /**
     * Display a listing of the Emails.
     *
     * @param type Emails $emails
     *
     * @return type view
     */
    public function index()
    {
        try {
            return view('themes.default1.admin.helpdesk.emails.emails.index');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Return emails list as DataTables JSON for the index AJAX endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmailList()
    {
        try {
            $default_system_email = Email::where('id', '=', '1')->first();
            $default_email = $default_system_email->sys_email ?? null;

            $emails = Emails::select('id', 'email_address', 'priority', 'department', 'created_at', 'updated_at')->get();

            return DataTables::of($emails)
                ->addColumn('email_address', function ($model) use ($default_email) {
                    $label = '<a href="'.route('emails.edit', $model->id).'">'.e($model->email_address).'</a>';
                    if ($default_email == $model->id) {
                        $label .= ' ( Default )';
                    }

                    return $label;
                })
                ->addColumn('priority', function ($model) {
                    if ($model->priority === null) {
                        return '<a href="'.url('getticket').'">System Default</a>';
                    }
                    $priority = Ticket_Priority::where('priority_id', '=', $model->priority)->first();

                    return $priority ? ucfirst($priority->priority_desc) : '-';
                })
                ->addColumn('department', function ($model) {
                    if ($model->department === null) {
                        return '<a href="'.url('getsystem').'">System Default</a>';
                    }
                    $dept = Department::where('id', '=', $model->department)->first();

                    return $dept ? e($dept->name) : '-';
                })
                ->addColumn('created_at', function ($model) {
                    return \UTC::usertimezone($model->created_at);
                })
                ->addColumn('updated_at', function ($model) {
                    return \UTC::usertimezone($model->updated_at);
                })
                ->addColumn('action', function ($model) use ($default_email) {
                    $edit = '<a href="'.route('emails.edit', $model->id).'" class="btn btn-primary btn-xs"><i class="fa-solid fa-edit"></i> '.\Lang::get('lang.edit').'</a> ';
                    if ($default_email == $model->id) {
                        $delete = '<button class="btn btn-danger btn-xs" disabled><i class="fa-solid fa-trash"></i> '.\Lang::get('lang.delete').'</button>';
                    } else {
                        $form_open = '<form method="POST" action="'.route('emails.destroy', $model->id).'" style="display:inline">'
                            .'<input type="hidden" name="_method" value="DELETE">'
                            .'<input type="hidden" name="_token" value="'.csrf_token().'">';
                        $delete = $form_open
                            .'<button type="submit" class="btn btn-danger btn-xs" onclick="return confirm(\'Are you sure?\')"><i class="fa-solid fa-trash"></i> '.\Lang::get('lang.delete').'</button>'
                            .'</form>';
                    }

                    return $edit.$delete;
                })
                ->rawColumns(['email_address', 'priority', 'department', 'created_at', 'updated_at', 'action'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param type Department      $department
     * @param type Help_topic      $help
     * @param type Priority        $priority
     * @param type MailboxProtocol $mailbox_protocol
     *
     * @return type Response
     */
    public function create(Department $department, Help_topic $help, Ticket_Priority $ticket_priority, MailboxProtocol $mailbox_protocol)
    {
        try {
            // fetch all the departments from the department table
            $departments = $department->get();
            // fetch all the helptopics from the helptopic table
            $helps = $help->where('status', '=', 1)->get();
            // fetch all the types of priority from the ticket_priority table
            $priority = $ticket_priority->where('status', '=', 1)->get();
            // fetch all the types of mailbox protocols from the mailbox_protocols table
            $mailbox_protocols = $mailbox_protocol->get();

            $service = new \App\Model\MailJob\MailService();
            $services = $service->pluck('name', 'id')->toArray();

            // return with all the table data
            return view('themes.default1.admin.helpdesk.emails.emails.create', compact('mailbox_protocols', 'priority', 'departments', 'helps', 'services'));
        } catch (Exception $e) {
            // return error messages if any
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Check for email input validation.
     *
     * @param EmailsRequest $request
     *
     * @return int
     */
    public function validatingEmailSettings(MailRequest $request, $id = '')
    {
        //dd($request->all());
        try {
            $service_request = $request->except('sending_status', '_token', 'email_address', 'email_name', 'user_name', 'password', 'department', 'priority', 'help_topic', 'fetching_protocol', 'fetching_host', 'fetching_port', 'fetching_encryption', 'imap_authentication', 'sending_protocol', 'sending_host', 'sending_port', 'sending_encryption', 'smtp_authentication', 'internal_notes', '_wysihtml5_mode', 'code');
            $service = $request->input('sending_protocol');
            $validate = '/novalidate-cert';
            $fetch = 1;
            $send = 1;
            //dd($request->input('fetching_status'));
            if ($request->input('fetching_status')) {
                $fetch = $this->getImapStream($request, $validate);
            }
            if ($request->input('sending_status') === 'on') {
                $this->emailService($service, $service_request);
                $send = $this->checkMail($request);
            }
            if ($send == 1 && $fetch == 1) {
                $this->store($request, $service_request, $id);

                return $this->jsonResponse('success', Lang::get('lang.successfully_saved_your_settings'));
            }

            return $this->validateEmailError($send, $fetch);
        } catch (Exception $ex) {
            dd($ex->getMessage());
            $message = $ex->getMessage();
            if ($request->input('fetching_status') && imap_last_error()) {
                $message = imap_last_error();
            }
            loging('mail-config', $message);

            return $this->jsonResponse('fails', $message);
        }
    }

    public function validateEmailError($out, $in)
    {
        if ($out !== 1) {
            return $this->jsonResponse('fails', Lang::get('lang.outgoing_email_connection_failed'));
        }
        if ($in !== 1) {
            return $this->jsonResponse('fails', Lang::get('lang.incoming_email_connection_failed_please_check_email_credentials_or_imap_settings'));
        }
    }

    public function jsonResponse($type, $message)
    {
        if ($type == 'fails') {
            $result = ['fails' => $message];
        }
        if ($type == 'success') {
            $result = ['success' => $message];
        }

        return response()->json(compact('result'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param type Emails        $email
     * @param type EmailsRequest $request
     *
     * @return type Redirect
     */
    public function store($request, $service_request = [], $id = '')
    {
        $email = new Emails();
        if ($id !== '') {
            $email = $email->find($id);
        }

        $email->email_address = $request->email_address;

        $email->email_name = $request->email_name;
        // stored as entered - deliberately NOT defaulted to the email address.
        // The edit form prefills this field from the column, so persisting a
        // resolved value would freeze the credential to whatever the address
        // was at creation time and keep using it after the address is changed.
        // Resolution happens at read time instead, in Emails::authUsername().
        $email->user_name = $request->user_name;
        $email->fetching_host = $request->fetching_host;
        $email->fetching_port = $request->fetching_port;
        $email->fetching_protocol = $request->fetching_protocol;
        $email->sending_host = $request->sending_host;
        $email->sending_port = $request->sending_port;
        $email->sending_protocol = $this->getDriver($request->sending_protocol);
        $email->sending_encryption = $request->sending_encryption;

        if ($request->smtp_validate == 'on') {
            $email->smtp_validate = $request->smtp_validate;
        }

        if ($request->input('password')) {
            $email->password = Crypt::encrypt($request->input('password'));
        }
        if ($request->input('fetching_status') == 'on') {
            $email->fetching_status = 1;
        } else {
            $email->fetching_status = 0;
        }
        if ($request->input('sending_status') == 'on') {
            $email->sending_status = 1;
        } else {
            $email->sending_status = 0;
        }
        if ($request->input('auto_response') == 'on') {
            $email->auto_response = 1;
        } else {
            $email->auto_response = 0;
        }
        $email->fetching_encryption = $request->input('fetching_encryption');
        if (!$request->input('imap_validate')) {
            $email->mailbox_protocol = 'novalidate-cert';
        }
        $email->department = $this->departmentValue($request->input('department'));
        // fetching priority value
        $email->priority = $this->priorityValue($request->input('priority'));
        // fetching helptopic value
        $email->help_topic = $this->helpTopicValue($request->input('help_topic'));
        // inserting the encrypted value of password
        $email->password = Crypt::encrypt($request->input('password'));
        $email->save(); // run save
        if ($request->input('fetching_status')) {
            $this->fetch($email);
        }
        if ($id === '') {
            // Creating a default system email as the first email is inserted to the system
            $email_settings = Email::where('id', '=', '1')->first();
            $email_settings->sys_email = $email->id;
            $email_settings->save();
        } else {
            $this->update($id, $request);
        }
        if (count($service_request) > 0) {
            $this->saveMailService($email->id, $service_request, $this->getDriver($request->sending_protocol));
        }
        if ($request->input('fetching_status')) {
            $this->fetch($email);
        }

        return 1;
    }

    public function checkMail($request)
    {
        $mailservice_id = $request->input('sending_protocol');
        $driver = $this->getDriver($mailservice_id);
        $address = $request->input('email_address');
        // same resolution the real send uses, so this test cannot pass with a
        // credential that sending would not use
        $username = Emails::resolveAuthUsername($request->input('user_name'), $address);
        $password = $request->input('password');
        $name = $request->input('email_name');
        $host = $request->input('sending_host');
        $port = $request->input('sending_port');
        $enc = $request->input('sending_encryption');
        $service_request = $request->except('sending_status', '_token', 'email_address', 'email_name', 'user_name', 'password', 'department', 'priority', 'help_topic', 'fetching_protocol', 'fetching_host', 'fetching_port', 'fetching_encryption', 'imap_authentication', 'sending_protocol', 'sending_host', 'sending_port', 'sending_encryption', 'smtp_authentication', 'internal_notes', '_wysihtml5_mode');

        $this->emailService($driver, $service_request);
        $this->setMailConfig($driver, $address, $name, $username, $password, $enc, $host, $port);
        $transport = new EsmtpTransport($host, $port);
        $transport->setUsername($username);
        $transport->setPassword($password);
        $transport->start();

        return 1;
    }

    public function sendDiagnoEmail($request)
    {
        $mailservice_id = $request->input('sending_protocol');
        $driver = $this->getDriver($mailservice_id);
        $address = $request->input('email_address');
        $username = Emails::resolveAuthUsername($request->input('user_name'), $address);
        $password = $request->input('password');
        $name = $request->input('email_name');
        $host = $request->input('sending_host');
        $port = $request->input('sending_port');
        $enc = $request->input('sending_encryption');
        $service_request = $request->except('sending_status', '_token', 'email_address', 'email_name', 'user_name', 'password', 'department', 'priority', 'help_topic', 'fetching_protocol', 'fetching_host', 'fetching_port', 'fetching_encryption', 'imap_authentication', 'sending_protocol', 'sending_host', 'sending_port', 'sending_encryption', 'smtp_authentication', 'internal_notes', '_wysihtml5_mode');

        $this->emailService($driver, $service_request);
        $this->setMailConfig($driver, $address, $name, $username, $password, $enc, $host, $port);
        $controller = new \App\Http\Controllers\Common\PhpMailController();
        $subject = 'test';
        $data = 'test';
        //dd(\Config::get('mail'),\Config::get('services'));
        $send = $controller->laravelMail($address, $name, $subject, $data, [], []);

        return $send;
    }

    public function setMailConfig($driver, $address, $name, $username, $password, $enc, $host, $port)
    {
        $configs = [
            'username'   => $username,
            'from'       => ['address' => $address, 'name' => $name],
            'password'   => $password,
            'encryption' => $enc,
            'host'       => $host,
            'port'       => $port,
            'driver'     => $driver,
        ];
        foreach ($configs as $key => $config) {
            if (is_array($config)) {
                foreach ($config as $from) {
                    \Config::set('mail.'.$key, $config);
                }
            } else {
                \Config::set('mail.'.$key, $config);
            }
        }
    }

    public function getDriver($driver_id)
    {
        $short = '';
        $email_drivers = new \App\Model\MailJob\MailService();
        $email_driver = $email_drivers->find($driver_id);
        if ($email_driver) {
            $short = $email_driver->short_name;
        }

        return $short;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param type int             $id
     * @param type Department      $department
     * @param type Help_topic      $help
     * @param type Emails          $email
     * @param type Priority        $priority
     * @param type MailboxProtocol $mailbox_protocol
     *
     * @return type Response
     */
    public function edit($id, Department $department, Help_topic $help, Emails $email, Ticket_Priority $ticket_priority, MailboxProtocol $mailbox_protocol)
    {
        try {
            $sys_email = \DB::table('settings_email')->select('sys_email')->where('id', '=', 1)->first();
            // dd($sys_email);
            // fetch the selected emails
            $emails = $email->whereId($id)->first();
            // get all the departments
            $departments = $department->get();
            //get count of emails
            $count = $email->count();
            // get all the helptopic
            $helps = $help->where('status', '=', 1)->get();
            // get all the priority
            $priority = $ticket_priority->where('status', '=', 1)->get();
            // get all the mailbox protocols
            $mailbox_protocols = $mailbox_protocol->get();

            $service = new \App\Model\MailJob\MailService();
            $services = $service->pluck('name', 'id')->toArray();

            // return if the execution is succeeded
            return view('themes.default1.admin.helpdesk.emails.emails.edit', compact('mailbox_protocols', 'priority', 'departments', 'helps', 'emails', 'sys_email', 'services'))->with('count', $count);
        } catch (Exception $e) {
            // return if try fails
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Check for email input validation.
     *
     * @param EmailsRequest $request
     *
     * @return int
     */
    public function validatingEmailSettingsUpdate($id, MailRequest $request)
    {
        try {
            return $this->validatingEmailSettings($request, $id);
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if (imap_last_error()) {
                $message = imap_last_error();
            }
            //dd($ex->getMessage());
            loging('mail-config', $message);

            //Log::error($ex->getMessage());
            return $this->jsonResponse('fails', $message);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type $id
     * @param type Emails            $email
     * @param type EmailsEditRequest $request
     *
     * @return type Response
     */
    public function update($id, $request)
    {
        try {
            if ($request->sys_email == 'on') {
                $system = \DB::table('settings_email')
                        ->where('id', '=', 1)
                        ->update(['sys_email' => $id]);
            } elseif ($request->input('count') <= 1 && $request->sys_email == null) {
                $system = \DB::table('settings_email')
                        ->where('id', '=', 1)
                        ->update(['sys_email' => null]);
            }
            $return = 1;
        } catch (Exception $e) {
            $return = $e->getMessage();
        }

        return $return;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param type int    $id
     * @param type Emails $email
     *
     * @return type Redirect
     */
    public function destroy($id, Emails $email)
    {
        // fetching the details on the basis of the $id passed to the function
        $default_system_email = Email::where('id', '=', '1')->first();
        if ($default_system_email->sys_email) {
            // checking if the default system email is the passed email
            if ($id == $default_system_email->sys_email) {
                return redirect('emails')->with('fails', Lang::get('lang.you_cannot_delete_system_default_email'));
            }
        }

        try {
            // fetching the database instance of the current email
            $emails = $email->whereId($id)->first();
            // checking if deleting the email is success or if it's carrying any dependencies
            $emails->delete();

            return redirect('emails')->with('success', Lang::get('lang.email_deleted_sucessfully'));
        } catch (Exception $e) {
            // returns if the try fails
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Create imap connection.
     *
     * @param type $request
     *
     * @return type int
     */
    public function getImapStream($request)
    {
        $host = $request->input('fetching_host');
        $port = $request->input('fetching_port');
        $service = $request->input('fetching_protocol');
        $encryption = $request->input('fetching_encryption');
        $validate = $request->input('imap_validate');
        // same resolution the real fetch uses
        $username = Emails::resolveAuthUsername($request->input('user_name'), $request->input('email_address'));
        $password = $request->input('password');
        $server = new Fetch($host, $port, $service);
        //$server->setFlag('novalidate-cert');
        if ($encryption != '') {
            $server->setFlag($encryption);
        }
        if (!$validate) {
            $server->setFlag('novalidate-cert');
        } else {
            $server->setFlag('validate-cert');
        }

        $server->setAuthentication($username, $password);
        $server->getImapStream();

        return 1;
    }

    /**
     * Check connection.
     *
     * @param type $imap_stream
     *
     * @return type int
     */
    public function checkImapStream($imap_stream)
    {
        $check_imap_stream = imap_check($imap_stream);
        if ($check_imap_stream) {
            $imap_stream = 1;
        } else {
            $imap_stream = 0;
        }

        return $imap_stream;
    }

    /**
     * Get smtp connection.
     *
     * @param type $request
     *
     * @return int
     */
    public function getSmtp($request)
    {
        $sending_status = $request->input('sending_status');
        // cheking for the sending protocol
        if ($request->input('sending_protocol') == 'smtp') {
            $mail = new \PHPMailer();
            $mail->isSMTP();
            $mail->Host = $request->input('sending_host');            // Specify main and backup SMTP servers
            //$mail->SMTPAuth = true;                                   // Enable SMTP authentication
            $mail->Username = $request->input('user_name');       // SMTP username
            $mail->Password = $request->input('password');            // SMTP password
            $mail->SMTPSecure = $request->input('sending_encryption'); // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $request->input('sending_port');            // TCP port to connect to
            if (!$request->input('smtp_validate')) {
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
                if ($mail->smtpConnect($mail->SMTPOptions) == true) {
                    $mail->smtpClose();
                    $return = 1;
                } else {
                    $return = 0;
                }
            } else {
                if ($mail->smtpConnect()) {
                    $mail->smtpClose();
                    $return = 1;
                } else {
                    $return = 0;
                }
            }
        } elseif ($request->input('sending_protocol') == 'mail') {
            $return = 1;
        }

        return $return;
    }

    /**
     * Checking if department value is null.
     *
     * @param type $dept
     *
     * @return type string or null
     */
    public function departmentValue($dept)
    {
        if ($dept) {
            $email_department = $dept;
        } else {
            $email_department = null;
        }

        return $email_department;
    }

    /**
     * Checking if priority value is null.
     *
     * @param type $priority
     *
     * @return type string or null
     */
    public function priorityValue($priority)
    {
        if ($priority) {
            $email_priority = $priority;
        } else {
            $email_priority = null;
        }

        return $email_priority;
    }

    /**
     * Checking if helptopic value is null.
     *
     * @param type $help_topic
     *
     * @return type string or null
     */
    public function helpTopicValue($help_topic)
    {
        if ($help_topic) {
            $email_help_topic = $help_topic;
        } else {
            $email_help_topic = null;
        }

        return $email_help_topic;
    }

    public function emailService($service, $value = [])
    {
        switch ($service) {
            case 'mailgun':
                $this->setServiceConfig($service, $value);
            case 'mandrill':
                $this->setServiceConfig($service, $value);
            case 'ses':
                $this->setServiceConfig($service, $value);
        }
    }

    public function setServiceConfig($service, $value)
    {
        //dd($service);
        if (count($value) > 0) {
            foreach ($value as $k => $v) {
                \Config::set("services.$service.$k", $v);
            }
        }
    }

    public function saveMailService($emailid, $request, $driver)
    {
        $mail_service = new \App\Model\MailJob\FaveoMail();
        $mails = $mail_service->where('email_id', $emailid)->get();
        if (count($request) > 0) {
            foreach ($mails as $mail) {
                $mail->delete();
            }
            foreach ($request as $key => $value) {
                $mail_service->create([
                    'drive'    => $driver,
                    'key'      => $key,
                    'value'    => $value,
                    'email_id' => $emailid,
                ]);
            }
        }
    }

    public function readMails()
    {
        $PhpMailController = new \App\Http\Controllers\Common\PhpMailController();
        $NotificationController = new \App\Http\Controllers\Common\NotificationController();
        $TicketController = new \App\Http\Controllers\Agent\helpdesk\TicketController($PhpMailController, $NotificationController);
        $TicketWorkflowController = new \App\Http\Controllers\Agent\helpdesk\TicketWorkflowController($TicketController);
        $controller = new \App\Http\Controllers\Agent\helpdesk\MailController($TicketWorkflowController);
        $emails = new Emails();
        $settings_email = new Email();
        $system = new \App\Model\helpdesk\Settings\System();
        $ticket = new \App\Model\helpdesk\Settings\Ticket();
        $controller->readmails($emails, $settings_email, $system, $ticket);
    }

    public function fetch($email)
    {
        $PhpMailController = new \App\Http\Controllers\Common\PhpMailController();
        $NotificationController = new \App\Http\Controllers\Common\NotificationController();
        $TicketController = new \App\Http\Controllers\Agent\helpdesk\TicketController($PhpMailController, $NotificationController);
        $TicketWorkflowController = new \App\Http\Controllers\Agent\helpdesk\TicketWorkflowController($TicketController);
        $controller = new \App\Http\Controllers\Agent\helpdesk\MailController($TicketWorkflowController);
        $controller->fetch($email);
    }
}
