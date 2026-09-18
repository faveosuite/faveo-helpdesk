<?php

namespace App\Http\Controllers\Agent\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\CannedRequest;
use App\Http\Requests\helpdesk\CannedUpdateRequest;
// model
use App\Model\helpdesk\Agent_panel\Canned;
use App\User;
// classes
use Exception;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * CannedController.
 *
 * This controller is for all the functionalities of Canned response for Agents in the Agent Panel
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class CannedController extends Controller
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
        // checking authentication
        $this->middleware('auth');
        // checking if role is agent
        $this->middleware('role.agent');
    }

    /**
     * Display a listing of the Canned Responses.
     *
     * @return type View
     */
    public function index()
    {
        try {
            return view('themes.default1.agent.helpdesk.canned.index');
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Return Canned Responses as Yajra DataTables JSON for the current user.
     *
     * @return type JSON
     */
    public function canned_list()
    {
        $canneds = Canned::where('user_id', '=', \Auth::user()->id)->get();

        return DataTables::of($canneds)
            ->addColumn('title', function ($model) {
                return $model->title;
            })
            ->addColumn('Actions', function ($model) {
                $view = '<a data-bs-toggle="modal" data-bs-target="#view'.$model->id.'" href="#" class="btn btn-info btn-sm" onclick="updateModelTitle(\''.addslashes($model->title).'\')">'.Lang::get('lang.view').'</a>';
                $edit = '<a href="'.route('canned.edit', $model->id).'" class="btn btn-primary btn-sm">'.Lang::get('lang.edit').'</a>';
                $delete = '<form method="POST" action="'.route('canned.destroy', $model->id).'" style="display:inline;">
                    '.csrf_field().'<input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm(\'Are you sure?\')">'.Lang::get('lang.delete').'</button>
                </form>';
                $modal = '<div class="modal fade" id="view'.$model->id.'">
                    <div class="modal-dialog"><div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"></h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body"><p><pre>'.e($model->message).'</pre></p></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.Lang::get('lang.close').'</button>
                        </div>
                    </div></div>
                </div>';

                return $view.' '.$edit.' '.$delete.$modal;
            })
            ->rawColumns(['Actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new Canned Response.
     *
     * @return type View
     */
    public function create()
    {
        try {
            return view('themes.default1.agent.helpdesk.canned.create');
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Store a newly created Canned Response.
     *
     * @param type CannedRequest $request
     * @param type Canned        $canned
     *
     * @return type Redirect
     */
    public function store(CannedRequest $request, Canned $canned)
    {
        try {
            // fetching all the requested inputs
            $canned->user_id = \Auth::user()->id;
            $canned->title = $request->input('title');
            $canned->message = $request->input('message');
            // saving inputs
            $canned->save();

            return redirect()->route('canned.list')->with('success', Lang::get('lang.added_successfully'));
        } catch (Exception $e) {
            return redirect()->route('canned.list')->with('fails', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the Canned Response.
     *
     * @param type $id
     * @param type Canned $canned
     *
     * @return type View
     */
    public function edit($id, Canned $canned)
    {
        try {
            // fetching requested canned response
            $canned = $canned->where('user_id', '=', \Auth::user()->id)->where('id', '=', $id)->first();

            return view('themes.default1.agent.helpdesk.canned.edit', compact('canned'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Update the Canned Response in database.
     *
     * @param type $id
     * @param type CannedUpdateRequest $request
     * @param type Canned              $canned
     *
     * @return type Redirect
     */
    public function update($id, CannedUpdateRequest $request, Canned $canned)
    {
        try {
            /* select the field where id = $id(request Id) */
            $canned = $canned->where('id', '=', $id)->where('user_id', '=', \Auth::user()->id)->first();
            // fetching all the requested inputs
            $canned->user_id = \Auth::user()->id;
            $canned->title = $request->input('title');
            $canned->message = $request->input('message');
            // saving inputs
            $canned->save();

            return redirect()->route('canned.list')->with('success', Lang::get('lang.updated_successfully'));
        } catch (Exception $e) {
            return redirect()->route('canned.list')->with('fails', $e->getMessage());
        }
    }

    /**
     * Delete the Canned Response from storage.
     *
     * @param type $id
     * @param type Canned $canned
     *
     * @return type Redirect
     */
    public function destroy($id, Canned $canned)
    {
        try {
            /* select the field where id = $id(request Id) */
            $canned = $canned->whereId($id)->first();
            /* delete the selected field */
            /* Check whether function success or not */
            $canned->delete();

            /* redirect to Index page with Success Message */
            return redirect()->route('canned.list')->with('success', Lang::get('lang.canned_response_deleted'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect()->route('canned.list')->with('fails', $e->getMessage());
        }
    }

    /**
     * Fetch Canned Response in the ticket detail page.
     *
     * @param type $id
     *
     * @return type json
     */
    public function get_canned($id)
    {
        // checking for the canned response with requested value
        if ($id != 'zzz') {
            // fetching canned response
            $canned = Canned::where('id', '=', $id)->where('user_id', '=', \Auth::user()->id)->first();
            $msg = $canned->message;
        } else {
            $msg = '';
        }

        // returning the canned response in JSON format
        return \Response::json($msg);
    }
}
