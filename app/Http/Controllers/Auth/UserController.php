<?php

namespace App\Http\Controllers\Agent\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// requests
/*  Include Sys_user Model  */
/* For validation include Sys_userRequest in create  */
/* For validation include Sys_userUpdate in update  */
use App\Http\Requests\helpdesk\Sys_userRequest;
/*  include guest_note model */
use App\Http\Requests\helpdesk\Sys_userUpdate;
// models
use App\Model\helpdesk\Agent_panel\Organization;
use App\Model\helpdesk\Agent_panel\User_org;
/* include User Model */
/* include Help_topic Model */
/* Profile validator */
/* Profile Password validator */
use App\User;
// classes
/* include ticket_thred model */
/* include tickets model */
/* TicketRequest to validate the ticket response */
/* Validate post check ticket */
use Illuminate\Support\Facades\Request;
use Redirect;
use Yajra\DataTables\Facades\DataTables;

/**
 * UserController.
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
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role.agent');
        // $this->middleware('roles');
    }

    /**
     * Display a listing of the resource.
     *
     * @param type User $user
     *
     * @return type Response
     */
    public function index()
    {
        try {
            /* get all values in Sys_user */
            return view('themes.default1.agent.helpdesk.user.index');
        } catch (Exception $e) {
            return view('404');
        }
    }

    /**
     * This function is used to display the list of users.
     *
     * @return datatable
     */
    public function user_list()
    {
        return DataTables::of(User::where('role', '!=', 'admin')->where('role', '!=', 'agent')->get())
                        ->addColumn('user_name', function ($model) {
                            return $model->user_name;
                        })
                        ->addColumn('email', function ($model) {
                            $email = $model->email;

                            return $email;
                        })
                        ->addColumn('phone', function ($model) {
                            $phone = '';
                            if ($model->phone_number) {
                                $phone = $model->ext.' '.$model->phone_number;
                            }
                            $mobile = '';
                            if ($model->mobile) {
                                $mobile = $model->mobile;
                            }
                            $phone = $phone.'&nbsp;&nbsp;&nbsp;'.$mobile;

                            return $phone;
                        })
                        ->addColumn('status', function ($model) {
                            $status = $model->active;
                            if ($status == 1) {
                                $stat = '<button class="btn btn-success btn-xs">Active</button>';
                            } else {
                                $stat = '<button class="btn btn-danger btn-xs">Inactive</button>';
                            }

                            return $stat;
                        })
                        ->addColumn('lastlogin', function ($model) {
                            $t = $model->updated_at;

                            return TicketController::usertimezone($t);
                        })
                        ->addColumn('Actions', function ($model) {
                            return '<span  data-toggle="modal" data-target="#deletearticle'.$model->id.'"><a href="#" ><button class="btn btn-danger btn-xs"></a> '.\Lang::get('lang.delete').' </button></span>&nbsp;<a href="'.route('user.edit', $model->id).'" class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a>&nbsp;<a href="'.route('user.show', $model->id).'" class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>
				<div class="modal fade" id="deletearticle'.$model->id.'">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Are You Sure ?</h4>
                </div>
                <div class="modal-body">
                '.$model->user_name.'
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal" id="dismis2">Close</button>
                    <a href="'.route('user.delete', $model->id).'"><button class="btn btn-danger">delete</button></a>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>';
                        })
                        ->rawColumns(['status', 'Actions'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return type Response
     */
    public function create()
    {
        try {
            return view('themes.default1.agent.helpdesk.user.create');
        } catch (Exception $e) {
            return view('404');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param type User            $user
     * @param type Sys_userRequest $request
     *
     * @return type Response
     */
    public function store(User $user, Sys_userRequest $request)
    {
        try {
            /* insert the input request to sys_user table */
            /* Check whether function success or not */
            $user->email = $request->input('email');
            $user->user_name = $request->input('full_name');
            $user->mobile = $request->input('mobile');
            $user->ext = $request->input('ext');
            $user->phone_number = $request->input('phone_number');
            $user->active = $request->input('active');
            $user->internal_note = $request->input('internal_note');
            $user->role = 'user';
            if ($user->save() == true) {
                /* redirect to Index page with Success Message */
                return redirect('user')->with('success', 'User  Created Successfully');
            } else {
                /* redirect to Index page with Fails Message */
                return redirect('user')->with('fails', 'User  can not Create');
            }
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', 'User  can not Create');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type Response
     */
    public function show($id, User $user)
    {
        try {
            /* select the field where id = $id(request Id) */
            $users = $user->whereId($id)->first();

            return view('themes.default1.agent.helpdesk.user.show', compact('users'));
        } catch (Exception $e) {
            return view('404');
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
    public function edit($id, User $user)
    {
        try {
            /* select the field where id = $id(request Id) */
            $users = $user->whereId($id)->first();

            return view('themes.default1.agent.helpdesk.user.edit', compact('users'));
        } catch (Exception $e) {
            return view('404');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int            $id
     * @param type User           $user
     * @param type Sys_userUpdate $request
     *
     * @return type Response
     */
    public function update($id, User $user, Sys_userUpdate $request)
    {
        try {
            /* select the field where id = $id(request Id) */
            $users = $user->whereId($id)->first();
            /* Update the value by selected field  */
            /* Check whether function success or not */
            if ($users->fill($request->except('active', 'role', 'is_delete', 'ban'))->save() == true) {
                /* redirect to Index page with Success Message */
                return redirect('user')->with('success', 'User  Updated Successfully');
            } else {
                /* redirect to Index page with Fails Message */
                return redirect('user')->with('fails', 'User  can not Update');
            }
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', 'User  can not Update');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param type int  $id
     * @param type User $user
     *
     * @return type Response
     */
    public function destroy($id, User $user)
    {
        try {
            /* select the field where id = $id(request Id) */
            $users = $user->whereId($id)->first();
            /* delete the selected field */
            /* Check whether function success or not */
            if ($users->delete() == true) {
                /* redirect to Index page with Success Message */
                return redirect('user')->with('success', 'User  Deleted Successfully');
            } else {
                /* redirect to Index page with Fails Message */
                return redirect('user')->with('fails', 'User  can not Delete');
            }
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('user')->with('fails', 'User  can not Delete');
        }
    }

    /**
     * User Assign Org.
     *
     * @param type $id
     *
     * @return type boolean
     */
    public function UserAssignOrg($id)
    {
        $org = Request::get('org');
        $user_org = new User_org();
        $user_org->org_id = $org;
        $user_org->user_id = $id;
        $user_org->save();

        return 1;
    }

    /**
     * user create organisation.
     *
     * @return type value
     */
    public function User_Create_Org($id)
    {
        if (Request::get('website') != null) {
            // checking website
            $check = Organization::where('website', '=', Request::get('website'))->first();
        } else {
            $check = null;
        }

        // checking name
        $check2 = Organization::where('name', '=', Request::get('name'))->first();

        if (Request::get('name') == null) {
            return 'Name is required';
        } elseif ($check2 != null) {
            return 'Name should be Unique';
        } elseif ($check != null) {
            return 'Website should be Unique';
        } else {
            $org = new Organization();
            $org->name = Request::get('name');
            $org->phone = Request::get('phone');
            $org->website = Request::get('website');
            $org->address = Request::get('address');
            $org->internal_notes = Request::get('internal');
            $org->save();

            $user_org = new User_org();
            $user_org->org_id = $org->id;
            $user_org->user_id = $id;
            $user_org->save();

            return 0;
        }
    }
}
