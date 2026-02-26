<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controllers
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\TeamRequest;
use App\Http\Requests\helpdesk\TeamUpdate;
// models
use App\Model\helpdesk\Agent\Assign_team_agent;
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Agent\Groups;
use App\Model\helpdesk\Agent\Teams;
use App\User;
// classes
use DB;
use Exception;
use Lang;
use Yajra\DataTables\Facades\DataTables;

/**
 * TeamController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class TeamController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return type void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('roles');
    }

    /**
     * get Index page.
     *
     * @param type Teams             $team
     * @param type Assign_team_agent $assign_team_agent
     *
     * @return type Response
     */
    public function index(Teams $team, Assign_team_agent $assign_team_agent)
    {
        try {
            $teams = $team->get();
            /*  find out the Number of Members in the Team */
            $id = $teams->pluck('id');
            $assign_team_agent = $assign_team_agent->get();

            return view('themes.default1.admin.helpdesk.agent.teams.index', compact('assign_team_agent', 'teams'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param type User $user
     *
     * @return type Response
     */
    public function create(User $user)
    {
        try {
            $user = $user->where('role', '<>', 'user')->where('active', '=', 1)->orderBy('first_name')->get();

            return view('themes.default1.admin.helpdesk.agent.teams.create', compact('user'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param type Teams       $team
     * @param type TeamRequest $request
     *
     * @return type Response
     */
    public function store(Teams $team, TeamRequest $request)
    {
        try {
            /* Check whether function success or not */
            $team->fill($request->except('team_lead'))->save();
            $team_update = Teams::find($team->id);
            if ($request->team_lead) {
                $team_lead = $request->team_lead;
                $team_update->update([
                    'team_lead' => $team_lead,
                ]);
                Assign_team_agent::create([
                    'team_id'  => $team_update->id,
                    'agent_id' => $team_lead,
                ]);
            } else {
                $team_lead = null;
            }

            /* redirect to Index page with Success Message */
            return redirect('teams')->with('success', Lang::get('lang.teams_created_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('teams')->with('fails', Lang::get('lang.teams_can_not_create').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param type $id
     * @param type User              $user
     * @param type Assign_team_agent $assign_team_agent
     * @param type Teams             $team
     *
     * @return type Response
     */
    public function show($id, User $user, Assign_team_agent $assign_team_agent, Teams $team)
    {
        try {
            $user = $user->whereId($id)->first();
            $teams = $team->whereId($id)->first();

            // $team_lead_name=User::whereId($teams->team_lead)->first();

            // $team_lead = $team_lead_name->first_name . " " . $team_lead_name->last_name;

            // $total_members = $assign_team_agent->where('team_id',$id)->count();

            return view('themes.default1.admin.helpdesk.agent.teams.show', compact('user', 'teams', 'id'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function getshow($id)
    {
        // dd($request);

        // $id = $request->input('show_id');

        // dd($id);

        $users = DB::table('team_assign_agent')->select('team_assign_agent.id', 'team_assign_agent.team_id', 'users.user_name', 'users.first_name', 'users.last_name', 'users.active', 'users.assign_group', 'users.primary_dpt', 'users.role')
          ->join('users', 'users.id', '=', 'team_assign_agent.agent_id')
          ->where('team_assign_agent.team_id', '=', $id);

//           ->get();
        // dd($users);
        return DataTables::of($users)
            ->addColumn('first_name', function ($model) {
                $full_name = ucfirst($model->first_name).' '.ucfirst($model->last_name);

                return $full_name;
            })

            ->addColumn('active', function ($model) {
                if ($model->active == '1') {
                    $role = "<a class='btn btn-success btn-xs'>".'Active'.'</a>';
                } elseif ($model->active == 'agent') {
                    $role = "<a class='btn btn-primary btn-xs'>".'Inactive'.'</a>';
                }

                return $role;
            })

            ->addColumn('assign_group', function ($model) {
                $group = Groups::whereId($model->assign_group)->first();

                return $group->name;
            })
            ->addColumn('primary_dpt', function ($model) {
                $dept = Department::whereId($model->primary_dpt)->first();

                return $dept->name;
            })
            ->addColumn('role', function ($model) {
                if ($model->role == 'admin') {
                    $role = "<a class='btn btn-success btn-xs'>".$model->role.'</a>';
                } elseif ($model->role == 'agent') {
                    $role = "<a class='btn btn-primary btn-xs'>".$model->role.'</a>';
                }

                return $role;
            })

            ->rawColumns(['active', 'role'])
            ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param type $id
     * @param type User              $user
     * @param type Assign_team_agent $assign_team_agent
     * @param type Teams             $team
     *
     * @return type Response
     */
    public function edit($id, User $user, Assign_team_agent $assign_team_agent, Teams $team)
    {
        try {
            $a_id = [];
            $teams = $team->whereId($id)->first();
            $agent_team = $assign_team_agent->where('team_id', $id)->get();
            $agent_id = $agent_team->pluck('agent_id', 'agent_id');
            foreach ($agent_id as $value) {
                array_push($a_id, $value);
            }
            // dd($a_id);
            $user = $user->whereIn('id', $a_id)->where('active', '=', 1)->orderBy('first_name')->get();

            // dd($user);
            return view('themes.default1.admin.helpdesk.agent.teams.edit', compact('agent_id', 'user', 'teams'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param type int        $id
     * @param type Teams      $team
     * @param type TeamUpdate $request
     *
     * @return type Response
     */
    public function update($id, Teams $team, TeamUpdate $request)
    {
        $teams = $team->whereId($id)->first();
        //updating check box
        if ($request->team_lead) {
            $team_lead = $request->team_lead;
        } else {
            $team_lead = null;
        }
        $teams->team_lead = $team_lead;
        $teams->save();

        $alert = $request->input('assign_alert');
        $teams->assign_alert = $alert;
        $teams->save(); //saving check box

        //updating whole field
        /* Check whether function success or not */
        try {
            $teams->fill($request->except('team_lead'))->save();

            /* redirect to Index page with Success Message */
            return redirect('teams')->with('success', Lang::get('lang.teams_updated_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('teams')->with('fails', Lang::get('lang.teams_can_not_update').'<li>'.$e->getMessage().'</li>');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param type int               $id
     * @param type Teams             $team
     * @param type Assign_team_agent $assign_team_agent
     *
     * @return type Response
     */
    public function destroy($id, Teams $team, Assign_team_agent $assign_team_agent)
    {
        try {
            $assign_team_agent->where('team_id', $id)->delete();
            $teams = $team->whereId($id)->first();
            $tickets = DB::table('tickets')->where('team_id', '=', $id)->update(['team_id' => null]);
            /* Check whether function success or not */
            $teams->delete();

            /* redirect to Index page with Success Message */
            return redirect('teams')->with('success', Lang::get('lang.teams_deleted_successfully'));
        } catch (Exception $e) {
            /* redirect to Index page with Fails Message */
            return redirect('teams')->with('fails', Lang::get('lang.teams_can_not_delete').'<li>'.$e->getMessage().'</li>');
        }
    }
}
