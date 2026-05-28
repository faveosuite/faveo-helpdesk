<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controllers
use App\Http\Controllers\Controller;
use App\Http\Requests\helpdesk\CompanyRequest;
// requests
use App\Http\Requests\helpdesk\EmailRequest;
use App\Http\Requests\helpdesk\SystemRequest;
use App\Model\helpdesk\Agent\Department;
// models
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Email\Template;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Notification\UserNotification;
use App\Model\helpdesk\Settings\Access;
use App\Model\helpdesk\Settings\Alert;
use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Settings\Responder;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Settings\Ticket;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Utility\Date_format;
use App\Model\helpdesk\Utility\Date_time_format;
use App\Model\helpdesk\Utility\Time_format;
use App\Model\helpdesk\Utility\Timezones;
use DateTime;
use DB;
// classes
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Support\Str;
use Lang;

/**
 * SettingsController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class SettingsController2 extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->smtp();
        $this->middleware('auth');
        $this->middleware('roles');
    }

    /**
     * Main Settings Page.
     *
     * @return type view
     */
    public function settings()
    {
        return view('themes.default1.admin.helpdesk.setting');
    }

    public function notificationSettings()
    {
        return view('themes.default1.admin.helpdesk.settings.notification');
    }

    public function deleteReadNoti()
    {
        $markasread = UserNotification::where('is_read', '=', 1)->get();
        foreach ($markasread as $mark) {
            $mark->delete();
            \App\Model\helpdesk\Notification\Notification::whereId($mark->notification_id)->delete();
        }

        return redirect()->back()->with('success', 'You have deleted all the read notifications');
    }

    public function deleteNotificationLog()
    {
        $days = Input::get('no_of_days');
        $date = new DateTime();
        $date->modify($days.' day');
        $formatted_date = $date->format('Y-m-d H:i:s');
        $markasread = DB::table('user_notification')->where('created_at', '<=', $formatted_date)->get();
        foreach ($markasread as $mark) {
            $mark->delete();
            \App\Model\helpdesk\Notification\Notification::whereId($mark->notification_id)->delete();
        }

        return redirect()->back()->with('success', 'You have deleted all the notification records since '.$days.' days.');
    }

    /**
     * @param int $id
     * @param     $compant instance of company table
     *
     * get the form for company setting page
     *
     * @return Response
     */
    public function getStatuses()
    {
        try {
            /* fetch the values of company from company table */
            $statuss = \DB::table('ticket_status')->get();

            /* Direct to Company Settings Page */
            return view('themes.default1.admin.helpdesk.settings.status', compact('statuss'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * @param int $id
     * @param     $compant instance of company table
     *
     * get the form for company setting page
     *
     * @return Response
     */
    public function editStatuses($id)
    {
        try {
            /* fetch the values of company from company table */
            $statuss = \App\Model\helpdesk\Ticket\Ticket_Status::whereId($id)->first();
            $statuss->name = Input::get('name');
            $statuss->icon_class = Input::get('icon_class');
            $statuss->email_user = Input::get('email_user');
            $statuss->sort = Input::get('sort');
            $delete = Input::get('delete');
            if ($delete == 'yes') {
                $statuss->state = 'delete';
            } else {
                $statuss->state = Input::get('state');
            }
            $statuss->sort = Input::get('sort');
            $statuss->save();

            /* Direct to Company Settings Page */
            return redirect()->back()->with('success', 'Status has been updated!');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    public function createStatuses(\App\Model\helpdesk\Ticket\Ticket_Status $statuss)
    {
        //        try {
        /* fetch the values of company from company table */
        $statuss->name = Input::get('name');
        $statuss->icon_class = Input::get('icon_class');
        $statuss->email_user = Input::get('email_user');
        $statuss->sort = Input::get('sort');
        $delete = Input::get('delete');
        if ($delete == 'yes') {
            $statuss->state = 'delete';
        } else {
            $statuss->state = Input::get('state');
        }
        $statuss->sort = Input::get('sort');
        $statuss->save();

        /* Direct to Company Settings Page */
        return redirect()->back()->with('success', 'Status has been created!');
//        } catch (Exception $ex) {
//            return redirect()->back()->with('fails', $ex->errorInfo[2]);
//        }
    }

    public function deleteStatuses($id)
    {
        try {
            if ($id > 5) {
                /* fetch the values of company from company table */
                \App\Model\helpdesk\Ticket\Ticket_Status::whereId($id)->delete();

                /* Direct to Company Settings Page */
                return redirect()->back()->with('success', 'Status has been deleted');
            } else {
                return redirect()->back()->with('failed', 'You cannot delete this status');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * @param int $id
     * @param     $compant instance of company table
     *
     * get the form for company setting page
     *
     * @return Response
     */
    public function getcompany(Company $company)
    {
        try {
            /* fetch the values of company from company table */
            $companys = $company->whereId('1')->first();

            /* Direct to Company Settings Page */
            return view('themes.default1.admin.helpdesk.settings.company', compact('companys'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int            $id
     * @param type Company        $company
     * @param type CompanyRequest $request
     *
     * @return Response
     */
    public function postcompany($id, Company $company, CompanyRequest $request)
    {
        /* fetch the values of company request  */
        $companys = $company->whereId('1')->first();
        if (Input::file('logo')) {
            $name = Input::file('logo')->getClientOriginalName();
            $destinationPath = 'uploads/company/';
            $fileName = rand(0000, 9999).'.'.$name;
            Input::file('logo')->move($destinationPath, $fileName);
            $companys->logo = $fileName;
        }
        if ($request->input('use_logo') == null) {
            $companys->use_logo = '0';
        }

        /* Check whether function success or not */
        try {
            $companys->fill($request->except('logo'))->save();

            /* redirect to Index page with Success Message */
            return redirect('getcompany')->with('success', 'Company Updated Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getcompany')->with('fails', 'Company can not Updated'.'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * function to delete system logo.
     *
     * @return type string
     */
    public function deleteLogo()
    {
        $allowed_dir = realpath(public_path('uploads/company'));

        $filename = basename(request()->get('data1', ''));

        if (empty($filename) || !$allowed_dir) {
            return 'false';
        }

        $path = $allowed_dir.DIRECTORY_SEPARATOR.$filename;

        if (!file_exists($path) || strpos(realpath($path), $allowed_dir) !== 0) {
            return 'false';
        }

        if (!unlink($path)) {
            return 'false';
        }

        $companys = Company::where('id', '=', 1)->first();
        $companys->logo = null;
        $companys->use_logo = '0';
        $companys->save();

        return 'true';
    }

    /**
     * get the form for System setting page.
     *
     * @param type System           $system
     * @param type Department       $department
     * @param type Timezones        $timezone
     * @param type Date_format      $date
     * @param type Date_time_format $date_time
     * @param type Time_format      $time
     *
     * @return type Response
     */
    public function getsystem(System $system, Department $department, Timezones $timezone, Date_format $date, Date_time_format $date_time, Time_format $time)
    {
        try {
            /* fetch the values of system from system table */
            $systems = $system->whereId('1')->first();
            /* Fetch the values from Department table */
            $departments = $department->get();
            /* Fetch the values from Timezones table */
            $timezones = $timezone->get();

            /* Direct to System Settings Page */
            return view('themes.default1.admin.helpdesk.settings.system', compact('systems', 'departments', 'timezones', 'time', 'date', 'date_time'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int           $id
     * @param type System        $system
     * @param type SystemRequest $request
     *
     * @return type Response
     */
    public function postsystem($id, System $system, SystemRequest $request)
    {
        try {
            // dd($request);
            /* fetch the values of system request  */
            $systems = $system->whereId('1')->first();
            /* fill the values to coompany table */
            /* Check whether function success or not */
            $systems->fill($request->input())->save();

            /* redirect to Index page with Success Message */
            return redirect('getsystem')->with('success', 'System Updated Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getsystem')->with('fails', 'System can not Updated'.'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for Ticket setting page.
     *
     * @param type Ticket     $ticket
     * @param type Sla_plan   $sla
     * @param type Help_topic $topic
     * @param type Priority   $priority
     *
     * @return type Response
     */
    public function getticket(Ticket $ticket, Sla_plan $sla, Help_topic $topic, Ticket_Priority $priority)
    {
        try {
            /* fetch the values of ticket from ticket table */
            $tickets = $ticket->whereId('1')->first();
            /* Fetch the values from SLA Plan table */
            $slas = $sla->get();
            /* Fetch the values from Help_topic table */
            $topics = $topic->get();

            /* Direct to Ticket Settings Page */
            return view('themes.default1.admin.helpdesk.settings.ticket', compact('tickets', 'slas', 'topics', 'priority'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int     $id
     * @param type Ticket  $ticket
     * @param type Request $request
     *
     * @return type Response
     */
    public function postticket($id, Ticket $ticket, Request $request)
    {
        try {
            /* fetch the values of ticket request  */
            $tickets = $ticket->whereId('1')->first();
            /* fill the values to coompany table */
            $tickets->fill($request->except('captcha', 'claim_response', 'assigned_ticket', 'answered_ticket', 'agent_mask', 'html', 'client_update'))->save();
            /* insert checkbox to Database  */
            $tickets->captcha = $request->input('captcha');
            $tickets->claim_response = $request->input('claim_response');
            $tickets->assigned_ticket = $request->input('assigned_ticket');
            $tickets->answered_ticket = $request->input('answered_ticket');
            $tickets->agent_mask = $request->input('agent_mask');
            $tickets->html = $request->input('html');
            $tickets->client_update = $request->input('client_update');
            $tickets->collision_avoid = $request->input('collision_avoid');
            /* Check whether function success or not */
            $tickets->save();

            /* redirect to Index page with Success Message */
            return redirect('getticket')->with('success', 'Ticket Updated Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getticket')->with('fails', 'Ticket can not Updated'.'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for Email setting page.
     *
     * @param type Email    $email
     * @param type Template $template
     * @param type Emails   $email1
     *
     * @return type Response
     */
    public function getemail(Email $email, Template $template, Emails $email1)
    {
        try {
            /* fetch the values of email from Email table */
            $emails = $email->whereId('1')->first();
            /* Fetch the values from Template table */
            $templates = $template->get();
            /* Fetch the values from Emails table */
            $emails1 = $email1->get();

            /* Direct to Email Settings Page */
            return view('themes.default1.admin.helpdesk.settings.email', compact('emails', 'templates', 'emails1'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int          $id
     * @param type Email        $email
     * @param type EmailRequest $request
     *
     * @return type Response
     */
    public function postemail($id, Email $email, EmailRequest $request)
    {
        try {
            /* fetch the values of email request  */
            $emails = $email->whereId('1')->first();
            /* fill the values to email table */
            $emails->fill($request->except('email_fetching', 'all_emails', 'email_collaborator', 'strip', 'attachment'))->save();
            /* insert checkboxes  to database */
            // $emails->email_fetching = $request->input('email_fetching');
            // $emails->notification_cron = $request->input('notification_cron');
            $emails->all_emails = $request->input('all_emails');
            $emails->email_collaborator = $request->input('email_collaborator');
            $emails->strip = $request->input('strip');
            $emails->attachment = $request->input('attachment');
            /* Check whether function success or not */
            $emails->save();

            /* redirect to Index page with Success Message */
            return redirect('getemail')->with('success', 'Email Updated Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getemail')->with('fails', 'Email can not Updated'.'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for cron job setting page.
     *
     * @param type Email    $email
     * @param type Template $template
     * @param type Emails   $email1
     *
     * @return type Response
     */
    public function getSchedular(Email $email, Template $template, Emails $email1)
    {
        // try {
        /* fetch the values of email from Email table */
        $emails = $email->whereId('1')->first();
        /* Fetch the values from Template table */
        $templates = $template->get();
        /* Fetch the values from Emails table */
        $emails1 = $email1->get();

        return view('themes.default1.admin.helpdesk.settings.crone', compact('emails', 'templates', 'emails1'));
        // } catch {

        // }
    }

    /**
     * Update the specified resource in storage for cron job.
     *
     * @param type Email        $email
     * @param type EmailRequest $request
     *
     * @return type Response
     */
    public function postSchedular(Email $email, Template $template, Emails $email1, Request $request)
    {
        // dd($request);
        try {
            /* fetch the values of email request  */
            $emails = $email->whereId('1')->first();
            if ($request->email_fetching) {
                $emails->email_fetching = $request->email_fetching;
            } else {
                $emails->email_fetching = 0;
            }
            if ($request->notification_cron) {
                $emails->notification_cron = $request->notification_cron;
            } else {
                $emails->notification_cron = 0;
            }
            $emails->save();

            /* redirect to Index page with Success Message */
            return redirect('job-scheduler')->with('success', Lang::get('lang.job-scheduler-success'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('job-scheduler')->with('fails', Lang::get('lang.job-scheduler-error').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for Access setting page.
     *
     * @param type Access $access
     *
     * @return type Response
     */
    // public function getaccess(Access $access) {
    // 	try {
    // 		/* fetch the values of access from access table */
    // 		$accesses = $access->whereId('1')->first();
    //	// 		 Direct to Access Settings Page
    // 		return view('themes.default1.admin.helpdesk.settings.access', compact('accesses'));
    // 	} catch (Exception $e) {
    // 		return view('404');
    // 	}
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param type Access  $access
     * @param type Request $request
     *
     * @return type Response
     */
    // public function postaccess(Access $access, Request $request) {
    // 	try {
    // 		/* fetch the values of access request  */
    // 		$accesses = $access->whereId('1')->first();
    // 		/* fill the values to access table */
    // 		$accesses->fill($request->except('password_reset', 'bind_agent_ip', 'reg_require', 'quick_access'))->save();
    // 		/* insert checkbox value to DB  */
    // 		$accesses->password_reset = $request->input('password_reset');
    // 		$accesses->bind_agent_ip = $request->input('bind_agent_ip');
    // 		$accesses->reg_require = $request->input('reg_require');
    // 		$accesses->quick_access = $request->input('quick_access');
    // 		/* Check whether function success or not */
    // 		if ($accesses->save() == true) {
    // 			/* redirect to Index page with Success Message */
    // 			return redirect('getaccess')->with('success', 'Access Updated Successfully');
    // 		} else {
    // 			/* redirect to Index page with Fails Message */
    // 			return redirect('getaccess')->with('fails', 'Access can not Updated');
    // 		}
    // 	} catch (Exception $e) {
    // 		/* redirect to Index page with Fails Message */
    // 		return redirect('getaccess')->with('fails', 'Access can not Updated');
    // 	}
    // }

    /**
     * get the form for Responder setting page.
     *
     * @param type Responder $responder
     *
     * @return type Response
     */
    public function getresponder(Responder $responder)
    {
        try {
            /* fetch the values of responder from responder table */
            $responders = $responder->whereId('1')->first();

            /* Direct to Responder Settings Page */
            return view('themes.default1.admin.helpdesk.settings.responder', compact('responders'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type Responder $responder
     * @param type Request   $request
     *
     * @return type
     */
    public function postresponder(Responder $responder, Request $request)
    {
        try {
            /* fetch the values of responder request  */
            $responders = $responder->whereId('1')->first();
            /* insert Checkbox value to DB */
            $responders->new_ticket = $request->input('new_ticket');
            $responders->agent_new_ticket = $request->input('agent_new_ticket');
            $responders->submitter = $request->input('submitter');
            $responders->participants = $request->input('participants');
            $responders->overlimit = $request->input('overlimit');
            /* fill the values to coompany table */
            /* Check whether function success or not */
            $responders->save();

            /* redirect to Index page with Success Message */
            return redirect('getresponder')->with('success', 'Responder Updated Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getresponder')->with('fails', 'Responder can not Updated'.'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * get the form for Alert setting page.
     *
     * @param type Alert $alert
     *
     * @return type Response
     */
    public function getalert(Alert $alert)
    {
        try {
            /* fetch the values of alert from alert table */
            $alerts = $alert->whereId('1')->first();

            /* Direct to Alert Settings Page */
            return view('themes.default1.admin.helpdesk.settings.alert', compact('alerts'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type $id
     * @param type Alert   $alert
     * @param type Request $request
     *
     * @return type Response
     */
    public function postalert($id, Alert $alert, Request $request)
    {
        try {
            /* fetch the values of alert request  */
            $alerts = $alert->whereId('1')->first();
            /* Insert Checkbox to DB */
            $alerts->assignment_status = $request->input('assignment_status');
            $alerts->ticket_status = $request->input('ticket_status');
            $alerts->overdue_department_member = $request->input('overdue_department_member');
            $alerts->sql_error = $request->input('sql_error');
            $alerts->excessive_failure = $request->input('excessive_failure');
            $alerts->overdue_status = $request->input('overdue_status');
            $alerts->overdue_assigned_agent = $request->input('overdue_assigned_agent');
            $alerts->overdue_department_manager = $request->input('overdue_department_manager');
            $alerts->internal_status = $request->input('internal_status');
            $alerts->internal_last_responder = $request->input('internal_last_responder');
            $alerts->internal_assigned_agent = $request->input('internal_assigned_agent');
            $alerts->internal_department_manager = $request->input('internal_department_manager');
            $alerts->assignment_assigned_agent = $request->input('assignment_assigned_agent');
            $alerts->assignment_team_leader = $request->input('assignment_team_leader');
            $alerts->assignment_team_member = $request->input('assignment_team_member');
            $alerts->system_error = $request->input('system_error');
            $alerts->transfer_department_member = $request->input('transfer_department_member');
            $alerts->transfer_department_manager = $request->input('transfer_department_manager');
            $alerts->transfer_assigned_agent = $request->input('transfer_assigned_agent');
            $alerts->transfer_status = $request->input('transfer_status');
            $alerts->message_organization_accmanager = $request->input('message_organization_accmanager');
            $alerts->message_department_manager = $request->input('message_department_manager');
            $alerts->message_assigned_agent = $request->input('message_assigned_agent');
            $alerts->message_last_responder = $request->input('message_last_responder');
            $alerts->message_status = $request->input('message_status');
            $alerts->ticket_organization_accmanager = $request->input('ticket_organization_accmanager');
            $alerts->ticket_department_manager = $request->input('ticket_department_manager');
            $alerts->ticket_department_member = $request->input('ticket_department_member');
            $alerts->ticket_admin_email = $request->input('ticket_admin_email');

            if ($request->input('system_error') == null) {
                $str = '%0%';
                $path = app_path('../config/app.php');
                $content = \File::get($path);
                $content = str_replace('%1%', $str, $content);
                \File::put($path, $content);
            } else {
                $str = '%1%';
                $path = app_path('../config/app.php');
                $content = \File::get($path);
                $content = str_replace('%0%', $str, $content);
                \File::put($path, $content);
            }
            /* fill the values to coompany table */
            /* Check whether function success or not */
            $alerts->save();

            /* redirect to Index page with Success Message */
            return redirect('getalert')->with('success', 'Alert Updated Successfully');
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('getalert')->with('fails', 'Alert can not Updated'.'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * 	To display the list of ratings in the system.
     *
     * @return type View
     */
    public function RatingSettings()
    {
        $ratings = DB::table('settings_ratings')->get();

        return view('themes.default1.admin.helpdesk.settings.ratings', compact('ratings'));
    }

    /**
     * 	To store rating data.
     *
     * @return type Redirect
     */
    public function PostRatingSettings($slug)
    {
        $name = Input::get('rating_name');
        $publish = Input::get('publish');
        $modify = Input::get('modify');
        DB::table('settings_ratings')->whereSlug($slug)->update(['rating_name' => $name, 'publish' => $publish, 'modify' => $modify]);

        return redirect()->back()->with('success', 'Successfully updated');
    }

    public function createRating()
    {
        $name = Input::get('rating_name');
        $publish = Input::get('publish');
        $modify = Input::get('modify');
        DB::table('settings_ratings')->insert(['rating_name' => $name, 'publish' => $publish, 'modify' => $modify]);

        return redirect()->back()->with('success', 'Successfully created this rating');
    }

    /**
     *  To delete a type of rating.
     *
     * @return type Redirect
     */
    public function RatingDelete($slug)
    {
        DB::table('settings_ratings')->whereSlug($slug)->delete();

        return redirect()->back()->with('success', 'Successfully Deleted');
    }

    /**
     *  Generate Api key.
     *
     * @return type json
     */
    public function generateApiKey()
    {
        $key = Str::random(32);

        return $key;
    }
}
