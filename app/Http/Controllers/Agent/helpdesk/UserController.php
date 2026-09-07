<?php

namespace App\Http\Controllers\Agent\helpdesk;

// controllers
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
// requests
/*  Include Sys_user Model  */
use App\Http\Requests\helpdesk\ChangepasswordRequest;
/* For validation include Sys_userRequest in create  */
use App\Http\Requests\helpdesk\OtpVerifyRequest;
/* For validation include Sys_userUpdate in update  */
use App\Http\Requests\helpdesk\ProfilePassword;
/*  include guest_note model */
use App\Http\Requests\helpdesk\ProfileRequest;
use App\Http\Requests\helpdesk\Sys_userRequest;
// change password request
use App\Http\Requests\helpdesk\Sys_userUpdate;
// models
use App\Model\helpdesk\Agent\Assign_team_agent;
use App\Model\helpdesk\Agent_panel\Organization;
use App\Model\helpdesk\Agent_panel\User_org;
use App\Model\helpdesk\Notification\Notification;
use App\Model\helpdesk\Notification\UserNotification;
use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\CountryCode;
use App\Model\helpdesk\Utility\Otp;
use App\User;
// classes
use Auth;
use DateTime;
use DB;
use Exception;
use GeoIP;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Lang;
use Redirect;

/**
 * UserController
 * This controller is used to CRUD an User details, and proile management of an agent.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class UserController extends Controller
{
    /**
     * Create a new controller instance.
     * constructor to check
     * 1. authentication
     * 2. user roles
     * 3. roles must be agent.
     *
     * @return void
     */
    public function __construct(PhpMailController $PhpMailController)
    {
        $this->PhpMailController = $PhpMailController;
        // checking authentication
        $this->middleware('auth');
        // checking if role is agent
        $this->middleware('role.agent');
    }

    /**
     * Display all list of the users.
     *
     * @param type User $user
     *
     * @return type view
     */
    public function index()
    {
        try {
            return view('themes.default1.agent.helpdesk.user.index');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function deletedUser()
    {
        try {
            // dd('here');
            /* get all values in Sys_user */
            return view('themes.default1.agent.helpdesk.user.deleteduser');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * This function is used to display the list of users using chumper datatables.
     *
     * @return datatable
     */
    public function user_list(Request $request)
    {
        $type = $request->input('profiletype');
        $search = $request->input('searchTerm');

        if ($type === 'agents') {
            $users = User::where('role', '=', 'agent')->where('is_delete', '=', 0);
        } elseif ($type === 'users') {
            $users = User::where('role', '=', 'user')->where('is_delete', '=', 0);
        } elseif ($type === 'active-users') {
            $users = User::where('role', '!=', 'admin')->where('active', '=', 1);
        } elseif ($type === 'inactive') {
            $users = User::where('role', '!=', 'admin')->where('active', '=', 0);
        } elseif ($type === 'deleted') {
            $users = User::where('role', '!=', 'admin')->where('is_delete', '=', 1);
        } elseif ($type === 'banned') {
            $users = User::where('role', '!=', 'admin')->where('ban', '=', 1);
        } else {
            $users = User::where('role', '!=', 'admin')->where('is_delete', '=', 0);
        }

        $users = $users->select('user_name', 'email', 'mobile', 'active', 'updated_at', 'role', 'id', 'last_name', 'country_code', 'phone_number');

        if ($search !== '') {
            $users = $users->where(function ($query) use ($search) {
                $query->where('user_name', 'LIKE', '%'.$search.'%');
                $query->orWhere('email', 'LIKE', '%'.$search.'%');
                $query->orWhere('first_name', 'LIKE', '%'.$search.'%');
                $query->orWhere('last_name', 'LIKE', '%'.$search.'%');
                $query->orWhere('mobile', 'LIKE', '%'.$search.'%');
                $query->orWhere('updated_at', 'LIKE', '%'.$search.'%');
                $query->orWhere('country_code', 'LIKE', '%'.$search.'%');
            });
        }

        // displaying list of users with chumper datatables
        // return \Datatable::collection(User::where('role', "!=", "admin")->get())
        return \Yajra\DataTables\Facades\DataTables::of($users)
                        /* column username */
                        ->removeColumn('id', 'last_name', 'country_code', 'phone_number')
                        ->addColumn('user_name', function ($model) {
                            if ($model->first_name) {
                                $string = strip_tags($model->first_name.' '.$model->last_name);
                            } else {
                                $string = strip_tags($model->user_name);
                            }
                            if (strlen($string) > 30) {
                                // truncate string
                                $stringCut = mb_substr($string, 0, 30, 'UTF-8').'...';
                            } else {
                                $stringCut = $string;
                            }

                            return "<a href='".route('user.show', $model->id)."' title='".$string."''>".$stringCut.'</a>';
                        })
                        /* column email */
                        ->addColumn('email', function ($model) {
                            $email = "<a href='".route('user.show', $model->id)."'>".e($model->email).'</a>';

                            return $email;
                        })
                        /* column phone */
                        ->addColumn('mobile', function ($model) {
                            $phone = '';
                            if ($model->phone_number) {
                                $phone = htmlspecialchars($model->ext.' '.$model->phone_number, ENT_QUOTES, 'UTF-8');
                            }
                            $mobile = '';
                            if ($model->mobile) {
                                $mobile = htmlspecialchars($model->mobile, ENT_QUOTES, 'UTF-8');
                            }
                            $phone = $phone.'&nbsp;&nbsp;&nbsp;'.$mobile;

                            return $phone;
                        })
                        /* column account status */
                        ->addColumn('active', function ($model) {
                            $status = $model->active;
                            if ($status == 1) {
                                $stat = '<button class="btn btn-success btn-xs">Active</button>';
                            } else {
                                $stat = '<button class="btn btn-danger btn-xs">Inactive</button>';
                            }

                            return $stat;
                        })
                        /* column last login date */
                        ->addColumn('updated_at', function ($model) {
                            $t = $model->updated_at;

                            return TicketController::usertimezone($t);
                        })
                        /* column Role */
                        ->addColumn('role', function ($model) {
                            $role = $model->role;

                            return $role;
                        })
                        /* column actions */
                        ->addColumn('Actions', function ($model) {
                            if ($model->is_delete == 0) {
                                return '<a href="'.route('user.edit', $model->id).'" class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a>&nbsp; <a href="'.route('user.show', $model->id).'" class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>';
                            } else {
                                if (Auth::user()->role == 'admin') {
                                    // @if(Auth::user()->role == 'admin')

                                    return '<a href="'.route('user.show', $model->id).'" class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>';
                                }

                                if (Auth::user()->role == 'agent') {
                                    // @if(Auth::user()->role == 'admin')
                                    if ($model->role == 'user') {
                                        return '<a href="'.route('user.show', $model->id).'" class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>';
                                    }
                                }
                            }
                        })
                        ->rawColumns(['user_name', 'email', 'mobile', 'active', 'updated_at', 'role', 'Actions'])
                        ->make();
    }

    public function restoreUser($id)
    {
        // dd($id);
        // $delete_all = Input::get('delete_all');
        $users = User::where('id', '=', $id)->first();
        $users->is_delete = 0;
        $users->active = 1;
        $users->ban = 0;
        $users->save();

        return redirect('user')->with('success', Lang::get('lang.user_restore_successfully'));
    }

    /**
     * Show the form for creating a new users.
     *
     * @return type view
     */
    public function create(CountryCode $code)
    {
        try {
            $settings = CommonSettings::select('status')->where('option_name', '=', 'send_otp')->first();
            $email_mandatory = CommonSettings::select('status')->where('option_name', '=', 'email_mandatory')->first();
            $location = GeoIP::getLocation();
            $phonecode = $code->where('iso', '=', $location->iso_code)->first();
            $org = Organization::pluck('name', 'id')->toArray();

            return view('themes.default1.agent.helpdesk.user.create', compact('org', 'settings', 'email_mandatory'))->with('phonecode', $phonecode->phonecode);
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->errorInfo[2]);
        }
    }

    /**
     * Store a newly created users in storage.
     *
     * @param type User            $user
     * @param type Sys_userRequest $request
     *
     * @return type redirect
     */
    public function store(User $user, Sys_userRequest $request)
    {
        /* insert the input request to sys_user table */
        /* Check whether function success or not */
        if ($request->input('email') != '') {
            $user->email = $request->input('email');
        } else {
            $user->email = null;
        }
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->user_name = $request->input('user_name');
        if ($request->input('mobile') != '') {
            $user->mobile = $request->input('mobile');
        } else {
            $user->mobile = null;
        }
        $user->ext = $request->input('ext');
        $user->phone_number = $request->input('phone_number');
        $user->country_code = $request->input('country_code');
        $user->active = $request->input('active');
        $user->internal_note = $request->input('internal_note');
        $password = $this->generateRandomString();
        $user->password = Hash::make($password);
        $user->role = 'user';

        try {
            if ($request->get('country_code') == '' && ($request->get('phone_number') != '' || $request->get('mobile') != '')) {
                return redirect()->back()->with(['fails' => Lang::get('lang.country-code-required-error'), 'country_code_error' => 1])->withInput();
            } else {
                $code = CountryCode::select('phonecode')->where('phonecode', '=', $request->get('country_code'))->get();
                if (!count($code)) {
                    return redirect()->back()->with(['fails' => Lang::get('lang.incorrect-country-code-error'), 'country_code_error' => 1])->withInput();
                }
            }
            // save user credentails
            if ($user->save() == true) {
                if ($request->input('org_id') != '') {
                    $orgid = $request->input('org_id');
                    $this->storeUserOrgRelation($user->id, $orgid);
                }
                // fetch user credentails to send mail
                $name = $user->first_name;
                $email = $user->email;
                if ($request->input('send_email')) {
                    try {
                        // send mail on registration
                        $this->PhpMailController->sendmail($from = $this->PhpMailController->mailfrom('1', '0'), $to = ['name' => $name, 'email' => $email], $message = ['subject' => null, 'scenario' => 'registration-notification'], $template_variables = ['user' => $name, 'email_address' => $email, 'user_password' => $password]);
                    } catch (Exception $e) {
                        // returns if try fails
                        return redirect('user')->with('warning', Lang::get('lang.user_send_mail_error_on_user_creation'));
                    }
                }
                // returns for the success case
                // returns for the success case
                $email_mandatory = CommonSettings::select('status')->where('option_name', '=', 'email_mandatory')->first();
                if (($request->input('active') == '0' || $request->input('active') == 0) || ($email_mandatory->status == '0') || $email_mandatory->status == 0) {
                    event(new \App\Events\LoginEvent($request));
                }

                return redirect('user')->with('success', Lang::get('lang.User-Created-Successfully'));
            }

//            $user->save();
            /* redirect to Index page with Success Message */
            return redirect('user')->with('success', Lang::get('lang.User-Created-Successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', $e->getMessage());
        }
    }

    /**
     * Random Password Genetor for users.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type view
     */
    public function randomPassword()
    {
        try {
            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890~!@#$%^&*(){}[]';
            $pass = []; //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 10; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }

            return implode($pass);
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', $e->getMessage());
        }
    }

    /**
     * Random Password Genetor for users.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type view
     */
    public function randomPostPassword($id, ChangepasswordRequest $request)
    {
        $authUser = \Auth::user();
        $targetUser = User::whereId($id)->first();
        if (!$targetUser) {
            return redirect('user')->with('fails', Lang::get('lang.user_not_found'));
        }
        // Only admins can change any user's password; agents can only change regular users' passwords
        if ($authUser->role !== 'admin' && in_array($targetUser->role, ['admin', 'agent'])) {
            return redirect('user')->with('fails', Lang::get('lang.you_are_not_authorized'));
        }

        try {
            $changepassword = $request->change_password;
            $user = $targetUser;
            $password = $request->change_password;
            $user->password = Hash::make($password);
            $user->save();
            $name = $user->first_name;
            $email = $user->email;

            $this->PhpMailController->sendmail($from = $this->PhpMailController
                    ->mailfrom('1', '0'), $to = ['name' => $name, 'email' => $email], $message = ['subject' => null, 'scenario' => 'reset_new_password'], $template_variables = ['user' => $name, 'user_password' => $password]);

            return redirect('user')->with('success', Lang::get('lang.password_change_successfully'));
        } catch (Exception $e) {
            return redirect('user')->with('fails', $e->getMessage());
        }
    }

    /**
     * @param type    $id
     * @param Request $request
     *
     * @return type
     */
    public function changeRoleAdmin($id, Request $request)
    {
        if (\Auth::user()->role !== 'admin') {
            return redirect('user')->with('fails', Lang::get('lang.you_are_not_authorized'));
        }

        try {
            $user = User::whereId($id)->first();
            $user->role = 'admin';
            $user->assign_group = $request->group;
            $user->primary_dpt = $request->primary_department;
            $user->save();

            return redirect('user')->with('success', Lang::get('lang.role_change_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', $e->getMessage());
        }
    }

    /**
     * @param type    $id
     * @param Request $request
     *
     * @return type
     */
    public function changeRoleAgent($id, Request $request)
    {
        try {
            $user = User::whereId($id)->first();
            $user->role = 'agent';
            $user->assign_group = $request->group;
            $user->primary_dpt = $request->primary_department;
            $user->save();

            return redirect('user')->with('success', Lang::get('lang.role_change_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', $e->getMessage());
        }
    }

    /**
     * @param type $id
     *
     * @return type
     */
    public function changeRoleUser($id)
    {
        try {
            $ticket = Tickets::where('assigned_to', '=', $id)->where('status', '=', '1')->get();
            if ($ticket) {
                $ticket = Tickets::where('assigned_to', '=', $id)->update(['assigned_to' => null]);
            }
            $user = User::whereId($id)->first();
            $user->role = 'user';
            $user->assign_group = null;
            $user->primary_dpt = null;
            $user->remember_token = null;
            $user->save();

            return redirect('user')->with('success', Lang::get('lang.role_change_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', $e->getMessage());
        }
        // return 'Agent Role Successfully Change to User';
    }

    /**
     * @param type $id
     *
     * @return type
     */
    public function deleteAgent($id)
    {
        // try {
        $delete_all = Input::get('delete_all');

        $delete_all = Input::get('delete_all');
        $users = User::where('id', '=', $id)->first();
        if ($users->role == 'user') {
            $users = User::where('id', '=', $id)->first();
            $users->is_delete = 1;
            $users->active = 0;
            $users->ban = 1;
            $users->save();

            return redirect('user')->with('success', Lang::get('lang.user_delete_successfully'));
        }
        // }

        if ($users->role == 'agent') {
            if ($delete_all == null) {
                $UserEmail = Input::get('assign_to');
                $assign_to = explode('_', $UserEmail);
                $ticket = Tickets::where('assigned_to', '=', $id)->get();
                if ($assign_to[0] == 'user') {
                    if ($users->id == $assign_to[1]) {
                        return redirect('user')->with('warning', Lang::get('lang.select_another_agent'));
                    }
                    // $user_detail = User::where('id', '=', $assign_to[1])->first();
                    // $assignee = $user_detail->first_name.' '.$user_detail->last_name;
                    // $ticket_logic1 = Tickets::where('assigned_to', '=', $id)
                    //             ->update(['assigned_to' => $assign_to[1]]);
                    // if ($ticket_logic2 = Tickets::where('user_id', '=', $id)->get()) {
                    //     $ticket_logic2 = Tickets::where('user_id', '=', $id)->update(['user_id' => $assign_to[1]]);
                    // }
                    // if ($ticket_logic3 = Ticket_Thread::where('user_id', '=', $id)->get()) {
                    //     $ticket_logic3 = Ticket_Thread::where('user_id', '=', $id)->update(['user_id' => $assign_to[1]]);
                    // }
                    // if ($ticket_logic4 = User_org::where('user_id', '=', $id)->get()) {
                    //     $ticket_logic4 = User_org::where('user_id', '=', $id)->update(['user_id' => $assign_to[1]]);
                    // }

                    // $thread2 = Ticket_Thread::where('ticket_id', '=', $ticket->id)->first();
                    // $thread2->body = 'This Ticket have been Reassigned to' .' '.  $assignee;
                    // $thread2->save();
                    // UserNotification::where('notification_id', '=', $ticket->id)->delete();
                    // $users = User::where('id', '=', $id)->get();
                    // $organization = User_org::where('user_id', '=', $id)->delete();
                    // Assign_team_agent::where('agent_id', '=', $id)->update(['agent_id' => $assign_to[1]]);
                    $tickets = Tickets::where('assigned_to', '=', $id)->get();

                    foreach ($tickets as $ticket) {
                        // code...

                        $ticket->assigned_to = $assign_to[1];
                        $user_detail = User::where('id', '=', $assign_to[1])->first();
                        $assignee = $user_detail->first_name.' '.$user_detail->last_name;
                        $ticket_number = $ticket->ticket_number;
                        $ticket->save();

                        $thread = new Ticket_Thread();
                        $thread->ticket_id = $ticket->id;
                        $thread->user_id = Auth::user()->id;
                        $thread->is_internal = 1;
                        $thread->body = 'This Ticket has been assigned to '.$assignee;
                        $thread->save();
                    }
                    $user = User::find($id);
                    $users->is_delete = 1;
                    $users->active = 0;
                    $users->ban = 1;
                    $users->save();

                    return redirect('user')->with('success', Lang::get('lang.agent_delete_successfully_and_ticket_assign_to_another_agent'));
                }

                // if (User_org::where('user_id', '=', $id)) {
                //     DB::table('user_assign_organization')->where('user_id', '=', $id)->delete();
                // }
                $user = User::find($id);
                $users->is_delete = 1;
                $users->active = 0;
                $users->ban = 1;
                $users->save();

                return redirect('user')->with('success', Lang::get('lang.agent_delete_successfully'));
            } elseif ($delete_all == 1) {
                if ($delete_all) {
                    // dd('here');
                    $tickets = Tickets::where('assigned_to', '=', $id)->get();
                    // dd($tickets);
                    foreach ($tickets as $ticket) {
                        $ticket->assigned_to = null;
                        $ticket_number = $ticket->ticket_number;
                        $ticket->save();

                        $thread = new Ticket_Thread();
                        $thread->ticket_id = $ticket->id;
                        $thread->user_id = Auth::user()->id;
                        $thread->is_internal = 1;
                        $thread->body = 'This Ticket has been unassigned ';
                        $thread->save();
                    }
                    // $users = User::where('id', '=', $id)->get();
                    $user = User::find($id);
                    $users->is_delete = 1;
                    $users->active = 0;
                    $users->save();

                    return redirect('user')->with('success', Lang::get('lang.agent_delete_successfully'));
                } else {
                    // Assign_team_agent::where('agent_id', '=', $id)->delete();
                    // User_org::where('user_id', '=', $id)->delete();
                    $user = User::find($id);
                    $users->is_delete = 1;
                    $users->active = 0;
                    $users->save();

                    return redirect('user')->with('success', Lang::get('lang.agent_delete_successfully'));
                }
            } else {
            }
        }
        // } catch (Exception $e) {
        /* redirect to Index page with Fails Message */
        // return redirect('user')->with('fails', $e->getMessage());
        // }
    }

    /**
     * Display the specified users.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type view
     */
    public function show($id)
    {
        try {
            $users = User::where('id', '=', $id)->first();
            if ($users && $users->count() > 0) {
                return view('themes.default1.agent.helpdesk.user.show', compact('users'));
            } else {
                return redirect()->back()->with('fails', Lang::get('lang.user-not-found'));
            }
        } catch (Exception $e) {
            dd($e);

            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type Response
     */
    public function edit($id, CountryCode $code)
    {
        try {
            // dd('here');
            $settings = CommonSettings::select('status')->where('option_name', '=', 'send_otp')->first();
            $email_mandatory = CommonSettings::select('status')->where('option_name', '=', 'email_mandatory')->first();

            $user = new User();
            /* select the field where id = $id(request Id) */
            $users = $user->whereId($id)->first();
            $location = GeoIP::getLocation();
            $phonecode = $code->where('iso', '=', $location->iso_code)->first();
            $orgs = Organization::all();
            // dd($org);
            $organization_id = User_org::where('user_id', '=', $id)->pluck('org_id')->first();

            // $org_name=Organization::where('id','=',$org_id)->pluck('name')->first();
            // dd($org_name);

            return view('themes.default1.agent.helpdesk.user.edit', compact('users', 'orgs', 'settings', 'email_mandatory', 'organization_id'))->with('phonecode', $phonecode->phonecode);
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param type int            $id
     * @param type User           $user
     * @param type Sys_userUpdate $request
     *
     * @return type Response
     */
    public function update($id, Sys_userUpdate $request)
    {
        $user = new User();
        /* select the field where id = $id(request Id) */
        $users = $user->whereId($id)->first();

        /* Update the value by selected field  */
        /* Check whether function success or not */
        try {
            if ($request->get('country_code') == '' && ($request->get('phone_number') != '' || $request->get('mobile') != '')) {
                return redirect()->back()->with(['fails' => Lang::get('lang.country-code-required-error'), 'country_code_error' => 1])->withInput();
            } else {
                $code = CountryCode::select('phonecode')->where('phonecode', '=', $request->get('country_code'))->get();
                if (!count($code)) {
                    return redirect()->back()->with(['fails' => Lang::get('lang.incorrect-country-code-error'), 'country_code_error' => 1])->withInput();
                } else {
                    $users->country_code = $request->country_code;
                }
            }
            $users->mobile = ($request->input('mobile') == '') ? null : $request->input('mobile');
            $users->fill($request->except('mobile', 'active', 'role', 'is_delete', 'ban'));
            $users->save();
            if ($request->input('org_id') != '') {
                $orgid = $request->input('org_id');

                $this->storeUserOrgRelation($id, $orgid);
            }

            /* redirect to Index page with Success Message */
            return redirect('user')->with('success', Lang::get('lang.User-profile-Updated-Successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * get agent profile page.
     *
     * @return type view
     */
    public function getProfile()
    {
        $user = Auth::user();

        try {
            return view('themes.default1.agent.helpdesk.user.profile', compact('user'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * get profile edit page.
     *
     * @return type view
     */
    public function getProfileedit(CountryCode $code)
    {
        $user = Auth::user();
        $location = GeoIP::getLocation();
        $phonecode = $code->where('iso', '=', $location->iso_code)->first();
        $settings = CommonSettings::select('status')->where('option_name', '=', 'send_otp')->first();
        $status = $settings->status;

        try {
            return view('themes.default1.agent.helpdesk.user.profile-edit', compact('user'))
                            ->with(['phonecode' => $phonecode->phonecode,
                                'verify'        => $status, ]);
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * post profile edit.
     *
     * @param type int            $id
     * @param type ProfileRequest $request
     *
     * @return type Redirect
     */
    public function postProfileedit(ProfileRequest $request)
    {
        try {
            // geet authenticated user details
            $user = Auth::user();
            if ($request->get('country_code') == '' && ($request->get('phone_number') != '' || $request->get('mobile') != '')) {
                return redirect()->back()->with(['fails' => Lang::get('lang.country-code-required-error'), 'country_code_error' => 1])->withInput();
            } else {
                $code = CountryCode::select('phonecode')->where('phonecode', '=', $request->get('country_code'))->get();
                if (!count($code)) {
                    return redirect()->back()->with(['fails' => Lang::get('lang.incorrect-country-code-error'), 'country_code_error' => 1])->withInput();
                }
                $user->country_code = $request->country_code;
            }
            $user->fill($request->except('profile_pic', 'mobile', 'active', 'role', 'is_delete', 'ban'));
            $user->gender = $request->input('gender');
            $user->save();
            if (Input::file('profile_pic')) {
                // fetching picture name
                $name = Input::file('profile_pic')->getClientOriginalName();
                // dd($name);
                // dd(str_replace(" ", "_", $name));
                // fetching upload destination path
                $destinationPath = 'uploads/profilepic';
                // adding a random value to profile picture filename
                $fileName = rand(0000, 9999).'.'.str_replace(' ', '_', $name);
                // moving the picture to a destination folder
                Input::file('profile_pic')->move($destinationPath, $fileName);
                // saving filename to database
                $user->profile_pic = $fileName;
            }
            if ($request->get('mobile')) {
                $user->mobile = $request->get('mobile');
            } else {
                $user->mobile = null;
            }
            if ($user->save()) {
                return Redirect::route('profile')->with('success', Lang::get('lang.Profile-Updated-sucessfully'));
            } else {
                return Redirect::route('profile')->with('fails', Lang::get('lang.Profile-Updated-sucessfully'));
            }
        } catch (Exception $e) {
            return Redirect::route('profile')->with('fails', $e->getMessage());
        }
    }

    /**
     * Post profile password.
     *
     * @param type int             $id
     * @param type ProfilePassword $request
     *
     * @return type Redirect
     */
    public function postProfilePassword($id, ProfilePassword $request)
    {
        // get authenticated user
        $user = Auth::user();
        // checking if the old password matches the new password
        if (Hash::check($request->input('old_password'), $user->getAuthPassword())) {
            $user->password = Hash::make($request->input('new_password'));

            try {
                $user->save();

                return redirect('profile-edit')->with('success1', Lang::get('lang.password_updated_sucessfully'));
            } catch (Exception $e) {
                return redirect('profile-edit')->with('fails', $e->getMessage());
            }
        } else {
            return redirect('profile-edit')->with('fails1', Lang::get('lang.password_was_not_updated_incorrect_old_password'));
        }
    }

    /**
     * Assigning an user to an organization.
     *
     * @param type $id
     *
     * @return type boolean
     */
    public function UserAssignOrg($id)
    {
        $org_name = Input::get('org');

        if ($org_name) {
            $org = Organization::where('name', '=', $org_name)->pluck('id')->first();
            if ($org) {
                $user_org = new User_org();
                $user_org->org_id = $org;
                $user_org->user_id = $id;
                $user_org->save();

                return 1;
            } else {
                return 0;
            }
        } else {
            return 2;
        }
    }

    public function UsereditAssignOrg($id)
    {
        $org_name = Input::get('org');

        if ($org_name) {
            $org = Organization::where('name', '=', $org_name)->pluck('id')->first();
            if ($org) {
                $user_org = User_org::where('user_id', '=', $id)->first();
                $user_org->org_id = $org;
                $user_org->user_id = $id;
                $user_org->save();

                return 1;
            } else {
                return 0;
            }
        } else {
            return 2;
        }
    }

    public function orgAssignUser($id)
    {
        $org = Input::get('org');
        $user_org = new User_org();
        $user_org->org_id = $id;
        $user_org->user_id = $org;
        $user_org->save();

        return 1;
    }

    public function removeUserOrg($id)
    {
        $user_org = User_org::where('org_id', '=', $id)->first();
        $user_org->delete();

        return redirect()->back()->with('success1', Lang::get('lang.the_user_has_been_removed_from_this_organization'));
    }

    /**
     * creating an organization in user profile page via modal popup.
     *
     * @param type $id
     *
     * @return type
     */
    public function User_Create_Org($id)
    {
        // checking if the entered value for website is available in database
        if (Input::get('website') != null) {
            // checking website
            $check = Organization::where('website', '=', Input::get('website'))->first();
        } else {
            $check = null;
        }
        // checking if the name is unique
        $check2 = Organization::where('name', '=', Input::get('name'))->first();
        // if any of the fields is not available then return false
        if (Input::get('name') == null) {
            return 'Name is required';
        } elseif ($check2 != null) {
            return 'Name should be Unique';
        } elseif ($check != null) {
            return 'Website should be Unique';
        } else {
            // storing organization details and assigning the current user to that organization
            $org = new Organization();
            $org->name = Input::get('name');
            $org->phone = Input::get('phone');
            $org->website = Input::get('website');
            $org->address = Input::get('address');
            $org->internal_notes = Input::get('internal');
            $org->save();

            $user_org = new User_org();
            $user_org->org_id = $org->id;
            $user_org->user_id = $id;
            $user_org->save();

            // for success return 0
            return 0;
        }
    }

    /**
     * Generate a random string for password.
     *
     * @param type $length
     *
     * @return string
     */
    public function generateRandomString($length = 10)
    {
        // list of supported characters
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // character length checked
        $charactersLength = strlen($characters);
        // creating an empty variable for random string
        $randomString = '';
        // fetching random string
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        // return random string
        return $randomString;
    }

    public function storeUserOrgRelation($userid, $orgid)
    {
        $org_relations = new User_org();
        $org_relation = $org_relations->where('user_id', $userid)->first();
        if ($org_relation) {
            $org_relation->delete();
        }
        $org_relations->create([
            'user_id' => $userid,
            'org_id'  => $orgid,
        ]);
    }

    public function getExportUser()
    {
        try {
            return view('themes.default1.agent.helpdesk.user.export');
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function exportUser(Request $request)
    {
        try {
            $date = $request->input('date');
            $date = str_replace(' ', '', $date);
            $date_array = explode(':', $date);
            $first = $date_array[0].' 00:00:00';
            $second = $date_array[1].' 23:59:59';
            $first_date = $this->convertDate($first);
            $second_date = $this->convertDate($second);
            $users = $this->getUsers($first_date, $second_date);
            $excel_controller = new \App\Http\Controllers\Common\ExcelController();
            $filename = 'users'.$date;

            return $excel_controller->export($filename, $users);
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function convertDate($date)
    {
        $converted_date = date('Y-m-d H:i:s', strtotime($date));

        return $converted_date;
    }

    public function getUsers($first, $last)
    {
        $user = new User();
        $users = $user->leftJoin('user_assign_organization', 'users.id', '=', 'user_assign_organization.user_id')
                ->leftJoin('organization', 'user_assign_organization.org_id', '=', 'organization.id')
                ->whereBetween('users.created_at', [$first, $last])
                ->where('role', 'user')
                ->where('active', 1)
                ->select('users.user_name as Username', 'users.email as Email', 'users.first_name as Fisrtname', 'users.last_name as Lastname', 'organization.name as Organization')
                ->get()
                ->toArray();

        return $users;
    }

    public function resendOTP(OtpVerifyRequest $request)
    {
        if (\Schema::hasTable('sms')) {
            $sms = DB::table('sms')->get();
            if (count($sms) > 0) {
                event(new \App\Events\LoginEvent($request));

                return 1;
            }
        } else {
            return 'Plugin has not been setup successfully.';
        }
    }

    public function verifyOTP()
    {
        // dd(Input::all());
        // $user = User::select('id', 'mobile', 'user_name')->where('email', '=', $request->input('email'))->first();
        $otp = Otp::select('otp', 'updated_at')->where('user_id', '=', Input::get('u_id'))
                ->first();
        if ($otp != null) {
            $otp_length = strlen(Input::get('otp'));
            if ($otp_length == 6 && !preg_match('/[a-z]/i', Input::get('otp'))) {
                $otp2 = Hash::make(Input::get('otp'));
                $date1 = date_format($otp->updated_at, 'Y-m-d h:i:sa');
                $date2 = date('Y-m-d h:i:sa');
                $time1 = new DateTime($date2);
                $time2 = new DateTime($date1);
                $interval = $time1->diff($time2);
                if ($interval->i > 10 || $interval->h > 0) {
                    $message = Lang::get('lang.otp-expired');

                    return $message;
                } else {
                    if (Hash::check(Input::get('otp'), $otp->otp)) {
                        Otp::where('user_id', '=', Input::get('u_id'))
                                ->update(['otp' => '']);

                        // User::where('id', '=', $user->id)
                        //     ->update(['active' => 1]);
                        // $this->openTicketAfterVerification($user->id);
                        return 1;
                    } else {
                        $message = Lang::get('lang.otp-not-matched');

                        return $message;
                    }
                }
            } else {
                $message = Lang::get('lang.otp-invalid');

                return $message;
            }
        } else {
            $message = Lang::get('lang.otp-not-matched');

            return $message;
        }
    }

    public function getAgentDetails()
    {
        $users = User::where('role', '<>', 'user')->where('active', '=', 1)->orderBy('first_name')->get();
        foreach ($users as $user) {
            echo "<option value='user_$user->id'>".$user->name().'</option>';
        }
    }
}
