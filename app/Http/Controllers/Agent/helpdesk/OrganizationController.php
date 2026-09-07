<?php

namespace App\Http\Controllers\Agent\helpdesk;

// controllers
use App\Http\Controllers\Controller;
use App\Http\Requests\helpdesk\OrganizationRequest;
// requests
use App\Http\Requests\helpdesk\OrganizationUpdate;
/* include organization model */
use App\Model\helpdesk\Agent_panel\Organization;
// models
/* Define OrganizationRequest to validate the create form */
use App\Model\helpdesk\Agent_panel\User_org;
use App\User;
/* Define OrganizationUpdate to validate the create form */
use Exception;
// classes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * OrganizationController
 * This controller is used to CRUD organization detail.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class OrganizationController extends Controller
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
        // checking for authentication
        $this->middleware('auth');
        // checking if the role is agent
        $this->middleware('role.agent');
        // only admin can delete organizations
        $this->middleware('roles')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @param type Organization $org
     *
     * @return type Response
     */
    public function index()
    {
        try {
            /* get all values of table organization */
            return view('themes.default1.agent.helpdesk.organization.index');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * This function is used to display the list of Organizations.
     *
     * @return datatable
     */
    public function org_list()
    {
        return DataTables::of(Organization::all())
                        ->addColumn('name', function ($model) {
                            // return $model->name;
                            if (strlen($model->name) > 20) {
                                $orgname = substr($model->name, 0, 25);
                                $orgname = substr($orgname, 0, strrpos($orgname, ' ')).' ...';
                            } else {
                                $orgname = $model->name;
                            }

                            return $orgname;
                        })
                        /* column website */
                        ->addColumn('website', function ($model) {
                            $website = $model->website;

                            return $website;
                        })
                        /* column phone number */
                        ->addColumn('phone', function ($model) {
                            $phone = $model->phone;

                            return $phone;
                        })
                        /* column action buttons */
                        ->addColumn('Actions', function ($model) {
                            return '<span data-bs-toggle="modal" data-bs-target="#deletearticle'.$model->id.'"><a href="#"><button class="btn btn-danger btn-xs"> '.\Lang::get('lang.delete').' </button></a></span>&nbsp;<a href="'.route('organizations.edit', $model->id).'" class="btn btn-warning btn-xs">'.\Lang::get('lang.edit').'</a>&nbsp;<a href="'.route('organizations.show', $model->id).'" class="btn btn-primary btn-xs">'.\Lang::get('lang.view').'</a>
				<div class="modal fade" id="deletearticle'.$model->id.'">
			        <div class="modal-dialog">
			            <div class="modal-content">
                			<div class="modal-header">
                                 <h4 class="modal-title">'.\Lang::get('lang.are_you_sure').'</h4>
                    			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                			</div>
                			<div class="modal-body">
                				'.$model->name.'
                			</div>
                			<div class="modal-footer justify-content-between">
                    			<button type="button" class="btn btn-default" data-bs-dismiss="modal" id="dismis2">'.\Lang::get('lang.close').'</button>
                    			<a href="'.route('org.delete', $model->id).'"><button class="btn btn-danger">'.\Lang::get('lang.delete').'</button></a>
                			</div>
            			</div><!-- /.modal-content -->
        			</div><!-- /.modal-dialog -->
    			</div>';
                        })
                        ->rawColumns(['Actions'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new organization.
     *
     * @return type Response
     */
    public function create()
    {
        try {
            return view('themes.default1.agent.helpdesk.organization.create');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Store a newly created organization in storage.
     *
     * @param type Organization        $org
     * @param type OrganizationRequest $request
     *
     * @return type Redirect
     */
    public function store(Organization $org, OrganizationRequest $request)
    {
        try {
            /* Insert the all input request to organization table */
            /* Check whether function success or not */
            if ($org->fill($request->input())->save() == true) {
                /* redirect to Index page with Success Message */
                return redirect('organizations')->with('success', Lang::get('lang.organization_created_successfully'));
            } else {
                /* redirect to Index page with Fails Message */
                return redirect('organizations')->with('fails', Lang::get('lang.organization_can_not_create'));
            }
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('organizations')->with('fails', Lang::get('lang.organization_can_not_create'));
        }
    }

    /**
     * Display the specified organization.
     *
     * @param type $id
     * @param type Organization $org
     *
     * @return type view
     */
    public function show($id, Organization $org)
    {
        try {
            /* select the field by id  */
            $orgs = $org->whereId($id)->first();

            /* To view page */
            return view('themes.default1.agent.helpdesk.organization.show', compact('orgs'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified organization.
     *
     * @param type $id
     * @param type Organization $org
     *
     * @return type view
     */
    public function edit($id, Organization $org)
    {
        try {
            /* select the field by id  */
            $orgs = $org->whereId($id)->first();

            /* To view page */
            return view('themes.default1.agent.helpdesk.organization.edit', compact('orgs'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified organization in storage.
     *
     * @param type $id
     * @param type Organization       $org
     * @param type OrganizationUpdate $request
     *
     * @return type Redirect
     */
    public function update($id, Organization $org, OrganizationUpdate $request)
    {
        try {
            /* select the field by id  */
            $orgs = $org->whereId($id)->first();
            /* update the organization table   */
            /* Check whether function success or not */
            if ($orgs->fill($request->input())->save() == true) {
                /* redirect to Index page with Success Message */
                return redirect('organizations')->with('success', Lang::get('lang.organization_updated_successfully'));
            } else {
                /* redirect to Index page with Fails Message */
                return redirect('organizations')->with('fails', Lang::get('lang.organization_can_not_update'));
            }
        } catch (Exception $e) {
            //            dd($e);
            /* redirect to Index page with Fails Message */
            return redirect('organizations')->with('fails', $e->getMessage());
        }
    }

    /**
     * Delete a specified organization from storage.
     *
     * @param type int $id
     *
     * @return type Redirect
     */
    public function destroy($id, Organization $org, User_org $user_org)
    {
        try {
            /* select the field by id  */
            $orgs = $org->whereId($id)->first();
            $user_orgs = $user_org->where('org_id', '=', $id)->get();
            foreach ($user_orgs as $user_org) {
                $user_org->delete();
            }
            /* Delete the field selected from the table */
            /* Check whether function success or not */
            $orgs->delete();

            /* redirect to Index page with Success Message */
            return redirect('organizations')->with('success', Lang::get('lang.organization_deleted_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('organizations')->with('fails', $e->getMessage());
        }
    }

    /**
     * Soring an organization head.
     *
     * @param type $id
     *
     * @return type boolean
     */
    public function Head_Org($id)
    {
        // get the user to make organization head
        $head_user = Input::get('user');
        // get an instance of the selected organization
        $org_head = Organization::where('id', '=', $id)->first();
        $org_head->head = $head_user;
        // save the user to organization head
        $org_head->save();

        return 1;
    }

    /**
     * get the report of organizations.
     *
     * @param type $id
     * @param type $date111
     * @param type $date122
     *
     * @return type array
     */
    public function orgChartData($id, $date111 = '', $date122 = '')
    {
        $date11 = strtotime($date122);
        $date12 = strtotime($date111);
        if ($date11 && $date12) {
            $date2 = $date12;
            $date1 = $date11;
        } else {
            // generating current date
            $date2 = strtotime(date('Y-m-d'));
            $date3 = date('Y-m-d');
            $format = 'Y-m-d';
            // generating a date range of 1 month
            $date1 = strtotime(date($format, strtotime('-1 month'.$date3)));
        }
        $return = '';
        $last = '';
        for ($i = $date1; $i <= $date2; $i = $i + 86400) {
            $thisDate = date('Y-m-d', $i);

            $user_orga_relation_id = [];
            $user_orga_relations = User_org::where('org_id', '=', $id)->get();
            foreach ($user_orga_relations as $user_orga_relation) {
                $user_orga_relation_id[] = $user_orga_relation->user_id;
            }
            $created = \DB::table('tickets')->select('created_at')->whereIn('user_id', $user_orga_relation_id)->where('created_at', 'LIKE', '%'.$thisDate.'%')->count();
            $closed = \DB::table('tickets')->select('closed_at')->whereIn('user_id', $user_orga_relation_id)->where('closed_at', 'LIKE', '%'.$thisDate.'%')->count();
            $reopened = \DB::table('tickets')->select('reopened_at')->whereIn('user_id', $user_orga_relation_id)->where('reopened_at', 'LIKE', '%'.$thisDate.'%')->count();

            $value = ['date' => $thisDate, 'open' => $created, 'closed' => $closed, 'reopened' => $reopened];
            $array = array_map('htmlentities', $value);
            $json = html_entity_decode(json_encode($array));
            $return .= $json.',';
        }
        $last = rtrim($return, ',');
        $users = User::whereId($id)->first();

        return '['.$last.']';
    }

    public function getOrgAjax(Request $request)
    {
        $org = new Organization();
        $q = $request->input('term');
        $orgs = $org->where('name', 'LIKE', '%'.$q.'%')
                ->select('name as label', 'id as value')
                ->get()
                ->toJson();

        return $orgs;
    }

    /**
     * This function is used autofill organizations name .
     *
     * @return datatable
     */
    public function organizationAutofill()
    {
        return view('themes.default1.agent.helpdesk.organization.getautocomplete');
    }
}
