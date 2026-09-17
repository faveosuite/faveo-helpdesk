<?php

namespace App\Http\Controllers\Agent\helpdesk\Filter;

//requests
use App\Http\Controllers\Agent\helpdesk\TicketController;
use App\Http\Controllers\Controller;
//controllers
use App\Http\Requests;
use App\Model\helpdesk\Agent\Department;
//models
use App\Model\helpdesk\Filters\Filter;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Ticket\TicketStatusType;
use App\User;
//classes
use Auth;
use DB;
use Illuminate\Http\Request;

/**
 * -----------------------------------------------------------------------------
 * FilterController
 * -----------------------------------------------------------------------------.
 *
 *
 * @author Manish Verma <manish.verma@ladybirdweb.com>
 * @copyright (c) 2017, Ladybird Web Solution
 *
 * @version v2.0
 */
class FilterController extends Controller
{
    protected $request;

    /**
     * @category constructor function
     *
     * @param Array/Object $request
     *
     * @return null
     */
    public function __construct(Request $req)
    {
        $this->middleware(['auth', 'role.agent']);
        $this->request = $req;
        $this->gmt = self::getGMT();
    }

    /**
     * @category function to handle ticket table/filteration request and build tables
     *
     * @param Array/Object $request
     *
     * @return json response //build by getTable() function in TicketController
     */
    public function getFilter(Request $request)
    {
        $table = $this->table();
        if ($request->has('segment')) {
            $segment = $this->request->input('segment');
            $table = $this->formatUserTickets($segment);
        } else {
            if ($request->has('api') && $request->get('api') == '1') {
                $inputs = [];
                foreach ($request->all() as $key => $value) {
                    if ($key != 'api' && $key != 'token' && $key != 'page' && $key
                            != 'sort-by' && $key != 'order' && $key != 'records_per_page') {
                        $inputs[$key] = explode(',', $value); //
                    }
                }

                return $table = $this->checkRequestIsCorrect($table, $inputs);
            } else {
                $inputs = json_decode(htmlspecialchars_decode($request->get('options')));
                // dd($inputs);
                $table = $this->checkRequestIsCorrect($table, (array) $inputs);
            }
        }

        return \Ttable::genreateTableJson($table);
    }

    /**
     * @category function to build basic query builder for ticket tables
     *
     * @param null
     *
     * @var
     *
     * @return builder $tickets
     */
    public function table()
    {
        $ticket = new Tickets();
        $tickets = $ticket
                        ->leftJoin('ticket_source', 'ticket_source.id', '=', 'tickets.source')
                        ->leftJoin('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                        ->leftJoin('users as u1', 'u1.id', '=', 'tickets.user_id')
                        ->leftJoin('teams', 'teams.id', '=', 'tickets.team_id')
                        ->leftJoin('users as u2', 'u2.id', '=', 'tickets.assigned_to')
                        ->leftJoin('ticket_collaborator', 'ticket_collaborator.ticket_id', '=', 'tickets.id')
                        ->leftJoin('ticket_thread as th', 'th.ticket_id', '=', 'tickets.id')
                        ->leftJoin('ticket_attachment', 'ticket_attachment.thread_id', '=', 'th.id')
                        ->select(
                            'tickets.id',
                            'th.title',
                            'tickets.ticket_number',
                            'u1.user_name as c_uname',
                            'u2.user_name as a_uname',
                            \DB::raw('CONVERT_TZ(max(th.updated_at), "+00:00", "'.$this->gmt.'") as updated_at2'),
                            \DB::raw('CONVERT_TZ(min(th.updated_at), "+00:00", "'.$this->gmt.'") as created_at2'),
                            \DB::raw('CONVERT_TZ(max(tickets.duedate), "+00:00", "'.$this->gmt.'") as duedate'),
                            \DB::raw('max(th.updated_at) as updated_at'),
                            \DB::raw('min(th.updated_at) as created_at'),
                            'tickets.duedate as due',
                            'u1.id as c_uid',
                            'ticket_priority.priority as priority',
                            'u1.first_name AS c_fname',
                            'u1.last_name as c_lname',
                            'u2.id as a_uid',
                            'u2.first_name as a_fname',
                            'u2.last_name as a_lname',
                            'u1.active as verified',
                            'teams.name',
                            'tickets.assigned_to',
                            'ticket_priority.priority_color as color',
                            'ticket_source.css_class as css',
                            \DB::raw('COUNT(ticket_attachment.thread_id) as countattachment'),
                            DB::raw('COUNT(ticket_collaborator.ticket_id) as countcollaborator'),
                            \DB::raw('COUNT(DISTINCT th.id) as countthread'),
                            \DB::raw('substring_index(group_concat(if(`th`.`is_internal` = 0, `th`.`poster`,null)ORDER By th.id desc) , ",", 1) as last_replier'),
                            \DB::raw('substring_index(group_concat(th.title order by th.id asc SEPARATOR "-||,||-") , "-||,||-", 1) as ticket_title'),
                            'ticket_source.name as source',
                            'tickets.closed'
                        )->groupby('tickets.id');

        return $tickets;
    }

    /**
     * @category function to check of all the parameters passed to the URL are correct or not
     *
     * @param array $inputs
     *
     * @return bool true/false
     */
    public function checkRequestIsCorrect($table, $inputs)
    {
        $available_options = [
            'show',
            'status',
            'types',
            'tags',
            'labels',
            'sla',
            'departments',
            'source',
            'created-by',
            'assigned',
            'assigned-to',
            'created',
            'updated',
            'due-on',
            'priority',
            'last-response-by',
            'ticket-number',
            'help-topic',
        ];
        $mytickets = false;
        if ($inputs['show'][0] == 'mytickets') {
            $mytickets = true;
        }
        ksort($inputs);
        foreach ($inputs as $key => $input) {
            if (!in_array($key, $available_options)) {
                // dd('here '.$key);
                $table = $table->where('tickets.id', '=', null);
            } else {
                $table = $this->filterByInputs($key, $input, $table, $mytickets);
            }
        }

        return $table;
    }

    /**
     * @category function to filter tickets based on user input requests
     *
     * @param string $input, $value, $table
     *
     * @return builder $table
     */
    public function filterByInputs($input, $value, $table, $is_mytickets)
    {
        switch ($input) {
            case 'show':
                $table = $this->showPage($value, $table);

                return $table;
                break;
            case 'status':
                $table = $this->filterByStatus($value, $table);

                return $table;
                break;

            case 'types':
                $table = $table->leftJoin('ticket_type', 'ticket_type.id', '=', 'tickets.type')
                        ->whereIn('ticket_type.name', $value);

                return $table;
                break;

            case 'tags':
                $table = $table
                        ->leftJoin('filters as tag', function ($join) {
                            $join->on('tickets.id', '=', 'tag.ticket_id')
                            ->where('tag.key', '=', 'tag');
                        })
                        ->whereIn('tag.value', $value);

                return $table;
                break;

            case 'labels':
                $table = $table
                        ->leftJoin('filters as label', function ($join) {
                            $join->on('tickets.id', '=', 'label.ticket_id')
                            ->where('label.key', '=', 'label');
                        })
                        ->whereIn('label.value', $value);

                return $table;
                break;

            case 'departments':
                $table = $this->departmentFilter($value, $table, $is_mytickets);

                return $table;
                break;

            case 'source':
                $table = $this->filterBySource($value, $table);

                return $table;
                break;

            case 'created-by':
                $users = [];
                foreach ($value as $username) {
                    array_push($users, substr($username, 2, strlen($username)));
                }
                $users = $this->getUserIDs($users, 'creator');
                if (count($users) > 0) {
                    $table = $table->whereIn('tickets.user_id', $users);
                } else {
                    $table = $table->where('tickets.id', '=', null);
                }

                return $table;
                break;

            case 'assigned':
                if ($value[0] == '') {
                    return $table;
                } else {
                    if ($value[0] == 0 || $value[0] == '0') {
                        $table = $table->where(function ($query) {
                            $query->where(function ($query2) {
                                $query2->where('tickets.team_id', '=', 0)
                                        ->orWhere('tickets.team_id', '=', null);
                            })->where(function ($query3) {
                                $query3->where('tickets.assigned_to', '=', 0)
                                        ->orWhere('tickets.assigned_to', '=', null);
                            });
                        });
                    } elseif ($value[0] == 1 || $value[0] == '1') {
                        $table = $table->where(function ($query) {
                            $query->where(function ($query2) {
                                $query2->where('tickets.team_id', '<>', 0)
                                        ->Where('tickets.team_id', '<>', null);
                            })->orWhere(function ($query3) {
                                $query3->where('tickets.assigned_to', '<>', 0)
                                        ->Where('tickets.assigned_to', '<>', null);
                            });
                        });
                    } else {
                        //do something here;
                    }

                    return $table;
                }
                break;

            case 'assigned-to':
                $table = $this->filterByAssigned($value, $table);

                return $table;
                break;

            case 'created':
                $table = $this->filterByDate('create', $value, $table);

                return $table;
                break;

            case 'updated':
                $table = $this->filterByDate('update', $value, $table);

                return $table;
                break;

            case 'due-on':
                $table = $this->filterByDate('due-today', $value, $table);

                return $table;
                break;

                // case 'show-overdue':
            //     $table = $this->filterByDate('overdue', $value, $table);
            //     return $table;
            //     break;

            case 'priority':
                $table = $this->filterByPriority($value, $table);

                return $table;
                break;

            case 'sla':
                $table = $this->filterBySla($value, $table);

                return $table;
                break;

            case 'last-response-by':
                $table = $this->filterByLastResponder($value, $table);

                return $table;
                break;
            case 'ticket-number':
                $table = $table->whereIn('tickets.ticket_number', $value);

                return $table;
                break;
            case 'help-topic':
                $table = $this->filterByHelpTopic($value, $table);

                return $table;
                break;
            default:
                break;
        }
    }

    /**
     * @category function to filter the tickets based on show value in the request
     *
     * @param array $value(), builder object $table
     *
     * @return builder object $table
     */
    public function showPage($value, $table)
    {
        $table = $this->userIsAgent($table);
        $has_status = array_key_exists('status', (array) json_decode(htmlspecialchars_decode($this->request->get('options'))));
        switch ($value[0]) {
            case 'inbox':
                return $this->returnShowPageWithStatus($has_status, $table, 'open');
                break;

            case 'mytickets':
                $table = $table->Where('tickets.assigned_to', '=', Auth::user()->id);

                return $this->returnShowPageWithStatus($has_status, $table, 'open');
                break;

            case 'trash':
                return $this->returnShowPageWithStatus($has_status, $table, 'deleted');
                break;

            case 'followup':
                $table = $table->where('tickets.follow_up', '=', 1);

                return $this->returnShowPageWithStatus($has_status, $table, 'open');
                break;

            case 'overdue':
                $table = $table->where('isanswered', '=', 0)
                        ->whereNotNull('tickets.duedate')
                        ->where('tickets.duedate', '!=', '00-00-00 00:00:00')
                        ->where('tickets.duedate', '<', \Carbon\Carbon::now());

                return $this->returnShowPageWithStatus($has_status, $table, 'open');

            case 'approval':
                return $this->returnShowPageWithStatus($has_status, $table, 'approval');
                break;

            case 'closed':
                return $this->returnShowPageWithStatus($has_status, $table, 'closed');

            default:
                $table = $table->where('tickets.id', '=', null);

                return $table;
                break;
        }
    }

    /**
     * @category function to update querybuilder according to user's role
     *
     * @param $table querybuilder
     *
     * @var, $dept
     *
     * @return $table
     */
    public function userIsAgent($table)
    {
        if (Auth::user()->role == 'agent') {
            $id = Auth::user()->id;
            $dept[] = Auth::user()->primary_dpt;
            $table = $table->where(function ($query) use ($dept) {
                $query->whereIn('tickets.dept_id', $dept)
                        ->orWhere('assigned_to', '=', Auth::user()->id);
            });
        }

        return $table;
    }

    /**
     * @category function to filter tickets builder based on agent/admin departments
     *
     * @param array $value //requested department, $table
     *
     * @var array
     *
     * @return $table
     */
    public function departmentFilter($value, $table, $is_mytickets)
    {
        if (count($value) == 1 && (strcasecmp($value[0], 'all') == 0)) {
            if ($is_mytickets) {
                $table = $table;
            } else {
                $table = $this->userIsAgent($table);
            }
        } else {
            $departmentTickets = $this->userCanSeeDepartmentTicket($value);
            if ($departmentTickets[0]) {
                $table = $table->leftJoin('department as dep', 'tickets.dept_id', '=', 'dep.id')
                        ->whereIn('dep.id', $departmentTickets[1]);
            } else {
                $table = $table->where('tickets.id', '=', null);
            }
        }

        return $table;
    }

    /**
     * @category function to return department ids and access right of departments for agents
     *
     * @param array $departments
     *
     * @var array
     *
     * @return array of boolean and array values
     */
    public function userCanSeeDepartmentTicket($departments)
    {
        $requested_dept = Department::whereIn('name', $departments)->pluck('id')->toArray();
        if (Auth::user()->role == 'admin') {
            return [true, $requested_dept];
        } else {
            $agent_dept = [Auth::user()->primary_dpt];
            if (count($requested_dept) > 0 && count($agent_dept) > 0) {
                return [count(array_intersect($requested_dept, $agent_dept)) == count($requested_dept), $requested_dept];
            }

            return [false, []];
        }
    }

    /**
     * @category function to filter and return ticket query builder based on priority
     *
     * @param array $priority, builder $table
     *
     * @var array
     *
     * @return builder
     */
    public function filterByPriority($priority, $table)
    {
        $priority_ids = Ticket_Priority::whereIn('priority', $priority)->pluck('priority_id')->toArray();
        if (count($priority_ids) > 0) {
            return $table->whereIn('tickets.priority_id', $priority_ids);
        }

        return $table->where('tickets.id', '=', null);
    }

    /**
     * @category function to filter and return builder by ticket creator
     *
     * @param string array $user_name, builder $table
     *
     * @var array
     *
     * @return filtered builder
     */
    public function getUserIDs($user_names, $user_type)
    {
        $query = DB::table('users')->where(function ($query) use ($user_names) {
            $query->whereIn('user_name', $user_names)
                    ->orWhereIn('email', $user_names);
        });
        if ($user_type == 'assign') {
            $query->where('role', '<>', 'user');
        } else {
            $query->where('role', '=', 'user');
        }
        $users = $query->pluck('id');

        return $users;
    }

    /**
     * @category function to fetch team id's where name is like given parameter
     *
     * @param array of string values $name
     *
     * @var, array $teams(all fetched teams id)
     *
     * @return array $teams
     */
    public function getTeamIds($name)
    {
        $query = DB::table('teams')->whereIn('name', $name);
        $teams = $query->pluck('id');

        return $teams;
    }

    /**
     * @category function to filter the tickets by assigned team or agents
     *
     * @param string array $name, builder $table
     *
     * @var array (stores id's of agents and admin),
     *            array $teams (stores ids of teams), array asssigned merged arrya of unique elements in $teams and $users
     *
     * @return builder $table
     */
    public function filterByAssigned($name, $table)
    {
        $team_array = [];
        $agent_array = [];
        foreach ($name as $value) {
            if (substr($value, 0, 2) == 'a-') {
                array_push($agent_array, substr($value, 2, strlen($value)));
            } elseif (substr($value, 0, 2) == 't-') {
                array_push($team_array, substr($value, 2, strlen($value)));
            }
        }

        $users = $this->getUserIDs($agent_array, 'assign');
        $teams = $this->getTeamIds($team_array);
        $table = $table->where(function ($query) use ($teams, $users) {
            $query->whereIn('tickets.team_id', $teams)
                    ->orWhereIn('tickets.assigned_to', $users);
        });

        // dd($table->toSql());
        return $table;
    }

    /**
     * @category function to filter table for various date option like created, last modified, duo date and overdue
     *
     * @param string $type (to check type of filter to apply on date), string $value for filters, builder $table
     *
     * @var array [start and end dates]
     *
     * @return builder
     */
    public function filterByDate($type, $value, $table)
    {
        switch ($type) {
            case 'create':
                $date = $this->getDate($value);
                $table = $this->getTableAfterDateFilration($date, $table, 'create', $value[0]);

                return $table;
                break;
            case 'update':
                $date = $this->getDate($value);
                $table = $this->getTableAfterDateFilration($date, $table, 'update', $value[0]);

                return $table;
                break;
            case 'due-today':
                $date = $this->getDate($value);
                $table = $this->getTableAfterDateFilration($date, $table, 'due', $value[0]);

                return $table;
                break;
                // case 'overdue':
            //     if ($value[0] == 1 || $value[0] == '1') {
            //         $table = $table->where('isanswered', '=', 0)
            //         ->whereNotNull('tickets.duedate')
            //         ->where('tickets.duedate', '!=', '00-00-00 00:00:00')
            //         ->where('tickets.duedate', '<', \Carbon\Carbon::now());
            //     }
            //     return $table;
            //     break;
        }
    }

    /**
     * @category function to get start and end date to apply date filter
     *
     * @param string $value
     *
     * @var date string, date string
     *
     * @return array of start and end date
     */
    public function getDate($value)
    {
        $dt = \Carbon\Carbon::now()->tz(timezone());
        switch ($value[0]) {
            case '5-minutes':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subMinutes(5)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '10-minutes':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subMinutes(10)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '15-minutes':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subMinutes(15)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '30-minutes':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subMinutes(30)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '1-hour':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subHour()->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '4-hours':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subHours(4)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '8-hours':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subHours(8)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '12-hours':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subHours(12)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '24-hours':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->subHours(24)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '15-days':
                $date = date('Y-m-d H:i:s');
                $end = carbon($date)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date)->subDays(15)->tz(timezone())->startOfDay()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case '30-days':
                $date_end = date('Y-m-d H:i:s');
                $date_start = date('Y-m-d 00:00:00');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_start)->subDays(30)->tz(timezone())->startOfDay()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'this-week':
                $date_end = date('Y-m-d H:i:s');
                $start = carbon($date_end)->tz(timezone())->startOfWeek()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'this-month':
                $date_end = date('Y-m-d H:i:s');
                $start = carbon($date_end)->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'this-year':
                $date_end = date('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->format('Y-m-d H:i:s');
                $start = carbon($date_end)->tz(timezone())->startOfYear()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'today':
                $date = date('Y-m-d H:i:s');
                $start = carbon($date)->tz(timezone())->startOfDay()->format('Y-m-d H:i:s');
                $end = carbon($date)->tz(timezone())->endOFDay()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'yesterday':
                $date = date('Y-m-d H:i:s');
                $end = carbon($date)->subDay()->tz(timezone())->endOFDay()->format('Y-m-d H:i:s');
                $start = carbon($date)->subDay()->tz(timezone())->startOfDay()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'next-hour':
                $date_start = date('Y-m-d H:i:s');
                $start = carbon($date_start)->tz('UTC')->format('Y-m-d H:i:s');
                $end = carbon($date_start)->addHour()->tz('UTC')->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'next-4-hours':
                $date_start = date('Y-m-d H:i:s');
                $start = carbon($date_start)->tz('UTC')->format('Y-m-d H:i:s');
                $end = carbon($date_start)->addHours(4)->tz('UTC')->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'next-8-hours':
                $date_start = date('Y-m-d H:i:s');
                $start = carbon($date_start)->tz('UTC')->format('Y-m-d H:i:s');
                $end = carbon($date_start)->addHours(8)->tz('UTC')->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'next-12-hours':
                $date_start = date('Y-m-d H:i:s');
                $start = carbon($date_start)->tz('UTC')->format('Y-m-d H:i:s');
                $end = carbon($date_start)->addHours(12)->tz('UTC')->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'tomorrow':
                $date = date('Y-m-d H:i:s');
                $end = carbon($date)->addDay()->tz(timezone())->endOfDay()->format('Y-m-d H:i:s');
                $start = carbon($date)->addDay()->tz(timezone())->startOfDay()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'last-week':
                $date_start = date('Y-m-d 00:00:00');
                $date_end = date('Y-m-d 23:59:59');
                $start = carbon($date_start)->subWeek()->tz(timezone())->startOfWeek()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->subWeek()->tz(timezone())->endOfWeek()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'last-month':
                $date_start = date('Y-m-d 00:00:00');
                $date_end = date('Y-m-d 23:59:59');
                $start = carbon($date_start)->subMonth()->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->subMonth()->tz(timezone())->endOfMonth()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'last-2-months':
                $date_start = date('Y-m-d 00:00:00');
                $date_end = date('Y-m-d 23:59:59');
                $start = carbon($date_start)->subMonths(2)->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'last-3-months':
                $date_start = date('Y-m-d 00:00:00');
                $date_end = date('Y-m-d 23:59:59');
                $start = carbon($date_start)->subMonths(3)->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'last-6-months':
                $date_start = date('Y-m-d 00:00:00');
                $date_end = date('Y-m-d 23:59:59');
                $start = carbon($date_start)->subMonths(6)->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->startOfMonth()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;

            case 'last-year':
                $date_start = date('Y-m-d 00:00:00');
                $date_end = date('Y-m-d 23:59:59');
                $start = carbon($date_start)->subYear()->tz(timezone())->startOfYear()->format('Y-m-d H:i:s');
                $end = carbon($date_end)->tz(timezone())->startOfYear()->format('Y-m-d H:i:s');

                return [$start, $end];
                break;
                // cases for due date

            case 'any-time':
                return ['any'];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * @category function to apply date filters in table builder after
     * getting start and end date based on the type of date filter
     *
     * @param array $dates, builder $table, $column (type of filter based on which column is being chosen), $value
     *
     * @var string (name of column), array
     *
     * @return builder
     */
    public function getTableAfterDateFilration($date, $table, $column, $value)
    {
        $check_column = '';
        $dates = [];
        if ($column == 'create') {
            $check_column = 'created_at2';
        } elseif ($column == 'update') {
            $check_column = 'updated_at2';
        } else {
            $check_column = 'tickets.duedate';
        }
        if (count($date) == 2) {
            $table = $table->having($check_column, '>=', $date[0])->having($check_column, '<=', $date[1]);
            if ($column == 'due') {
                $table = $table->where('isanswered', '=', 0);
            }
        } elseif (count($date) == 1) {
            //do nothing
        } else {
            $table = $table->where('tickets.id', '=', null);
        }

        // dd($table->toSql());
        return $table;
    }

    /**
     * @category function to filter ticket by source of creation
     *
     * @param array $name of source, builder $table
     *
     * @var array
     *
     * @return builder
     */
    public function filterBySource($source_names, $table)
    {
        $sources = DB::table('ticket_source')->whereIn('name', $source_names)->orWhereIn('value', $source_names)->pluck('id');
        if (count($sources) == 0) {
            return $table->where('tickets.id', '=', null);
        }

        return $table->whereIn('tickets.source', $sources);
    }

    /**
     *    DEPRICATED.
     *
     * @category function to get array of status to filter tickets
     *
     * @param string $status
     *
     * @return array $status_array
     */
    // public function getStatusArray($status)
    // {
    //     $type = new TicketStatusType();
    //     $values = $type->select('name', 'id')
    //             ->whereIn('name', [$status])
    //             ->with(['status' => function ($query) {
    //                 $query->select('id as status_id', 'name', 'purpose_of_status');
    //             }])
    //             ->get()
    //             ->pluck('status')
    //             ->flatten()
    //             ->pluck('status_id')
    //             ->toArray()
    //         ;
    //     return $values;
    // }

    /**
     * @category function to filter tickets by SLA
     *
     * @param string array $value, builder $table
     *
     * @
     *
     * @return builder
     */
    public function filterBySla($value, $table)
    {
        $query = DB::table('sla_plan');
        foreach ($value as $sla) {
            $query->orWhere('name', '=', $sla);
        }
        $sla_ids = $query->pluck('id');
        if (count($sla_ids) > 0) {
            return $table->whereIn('tickets.sla', $sla_ids);
        }

        return $table->where('tickets.id', '=', null);
    }

    /**
     * @category function to filter table builder based on requested status
     *
     * @param string array $status_array, builder $table
     *
     * @return builder $table
     */
    public function filterByStatus($status_array, $table)
    {
        $status = DB::table('ticket_status')->whereIn('name', $status_array)->pluck('id');
        if (count($status) > 0) {
            return $table->whereIn('tickets.status', $status);
        } else {
            return $table->where('tickets.id', '=', null);
        }
    }

    /**
     * @category function to format and return user tickets
     *
     * @param string $segment
     *
     * @return builder
     */
    public function formatUserTickets($segment)
    {
        $table = $this->table();
        $convert_to_array = explode('/', $segment);
        if ($convert_to_array[1] == 'user') {
            $user_id = $convert_to_array[2];
            $user = \DB::table('users')->select('role', 'id')->where('id', '=', $user_id)->first();
            if ($user->role == 'user') {
                $table = $table->where('tickets.user_id', '=', $user->id);
            } else {
                $table = $table->where('tickets.assigned_to', '=', $user->id);
            }
        } elseif ($convert_to_array[1] == 'organizations') {
            $users = []; //initialize by assuming there is no user in the organization
            $organizations_details = \App\Model\helpdesk\Agent_panel\Organization::select('id', 'domain')->where('id', '=', $convert_to_array[2])->first()->toArray(); //fetch organization details
            if (count($organizations_details) > 0) { //if organizationdetails found then process further
                $org_users = \App\Model\helpdesk\Agent_panel\User_org::select('user_id')->where('org_id', '=', $organizations_details['id'])->get()->toArray();
                if (count($org_users) > 0) {
                    $users = array_column($org_users, 'user_id');
                }
                if ($organizations_details['domain'] != '') {
                    $str = str_replace(',', '|@', '@'.$organizations_details['domain']);
                    $domain_users = User::select('id')->where('role', '=', 'user')->whereRaw("email REGEXP '".$str."'")->whereNOtIn('id', $users);
                    $domain_users = $domain_users->where('is_delete', '!=', 1)->where('ban', '!=', 1)->get()->toArray();
                    if (count($domain_users) > 0) {
                        $users = array_merge($users, array_column($domain_users, 'id'));
                    }
                }
            }
            $table = $table->whereIn('tickets.user_id', $users);
        } elseif ($convert_to_array[1] == 'department') {
            $table = $table->where('dept_id', '=', $convert_to_array[2]);
        } else {
            $table = $table->where('team_id', '=', $convert_to_array[2]);
        }

        return $table->whereIn('tickets.status', getStatusArray($convert_to_array[3]));
    }

    /**
     * @category function to filter results on basis of last replier
     *
     * @param string array $value, builder $ticket
     *
     * @return builder
     */
    public function filterByLastResponder($value, $tickets)
    {
        if ($value[0] == 'Client') {
            return $tickets->having('last_replier', '=', 'Client');
        } else {
            return $tickets->having('last_replier', '<>', 'Client');
        }
    }

    /**
     * @category function to get GMT for system timezone
     *
     * @param null
     *
     * @var, $tz
     *
     * @return string GMT value of timezone
     */
    public function getGMT()
    {
        $system = \App\Model\helpdesk\Settings\System::select('time_zone')->first();
        $timezone = \DB::table('timezone')->select('location')->where('id', '=', $system->time_zone)->first();
        $location = '(GMT) London';
        if ($timezone) {
            $location = $timezone->location;
        }
        $tz = explode(')', substr($location, stripos($location, 'T')
                            + 1));

        return ($tz[0] != '') ? $tz[0] : '+00:00';
    }

    /**
     * @category function to apply help topic filter
     *
     * @param array $value, object $table
     *
     * @return builder
     */
    public function filterByHelpTopic($value, $table)
    {
        $help_topics = Help_topic::whereIn('topic', $value)->pluck('id')->toArray();

        return $table->whereIn('help_topic_id', $help_topics);
    }

    /**
     * @category function to return builder for show filter after checking if input
     * request has status or not
     *
     * @param bool $has_status(if request has status filter values or not),
     *                            Object $table, string $status(basic pupose if status)
     *
     * @return object $table;
     */
    public function returnShowPageWithStatus($has_status, $table, $status)
    {
        if ($has_status) {
            return $table;
        }

        return $table->whereIn('tickets.status', getStatusArray($status));
    }
}
