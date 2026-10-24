<?php

namespace App\Api\v1;

use App\Http\Controllers\Agent\helpdesk\TicketController as CoreTicketController;
use App\Http\Controllers\Controller;
use App\Http\Requests\helpdesk\CreateTicketRequest;
use App\Http\Requests\helpdesk\TicketRequest;
use App\Model\helpdesk\Agent\Department;
//use Illuminate\Support\Facades\Request as Value;
use App\Model\helpdesk\Agent\Teams;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\Priority;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Support\Str;
use Lang;

/**
 * -----------------------------------------------------------------------------
 * Api Controller
 * -----------------------------------------------------------------------------.
 *
 *
 * @author Vijay Sebastian <vijay.sebastian@ladybirdweb.com>
 * @copyright (c) 2016, Ladybird Web Solution
 *
 * @name Faveo HELPDESK
 *
 * @version v1
 */
class ApiController extends Controller
{
    public $user;

    public $request;

    public $ticket;

    public $model;

    public $thread;

    public $attach;

    public $ticketRequest;

    public $faveoUser;

    public $team;

    public $setting;

    public $helptopic;

    public $slaPlan;

    public $department;

    public $priority;

    public $source;

    /**
     * The only endpoints a customer ('user' role) token may call. Everything
     * else in this controller is staff-only - see the gate in the constructor.
     *
     * Keep this list short: a method added to this controller is staff-only
     * unless it is deliberately listed here, and a listed method must check
     * object ownership itself (as getMyTicketsUser() does).
     */
    private const CUSTOMER_METHODS = ['getMyTicketsUser', 'getHelpTopic', 'getSlaPlan', 'getPriority', 'getDepartment'];

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->middleware('jwt.authOveride');
        $this->middleware('api', ['except' => 'GenerateApiKey']);
        // Staff-only by default. The v1 API is the agent API, but 'jwt.authOveride'
        // and the shared api_key only prove the caller is *someone*: any customer
        // token used to reach ticket lists, other customers' tickets and internal
        // notes, the customer directory and so on. Registered after the JWT
        // middleware, so the token has already been validated here.
        $this->middleware(function ($request, $next) {
            if ($deny = $this->denyUnlessStaff($this->authenticatedApiUser())) {
                return $deny;
            }

            return $next($request);
        })->except(self::CUSTOMER_METHODS);

        try {
            $user = \JWTAuth::parseToken()->authenticate();
            $this->user = $user;
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        }

        $ticket = new TicketController();
        $this->ticket = $ticket;

        $model = new Tickets();
        $this->model = $model;

        $thread = new Ticket_Thread();
        $this->thread = $thread;

        $attach = new Ticket_attachments();
        $this->attach = $attach;

        $ticketRequest = new TicketRequest();
        $this->ticketRequest = $ticketRequest;

        $faveoUser = new User();
        $this->faveoUser = $faveoUser;

        $faveoUser = new User();
        $this->user = $faveoUser;

        $team = new Teams();
        $this->team = $team;

        $setting = new System();
        $this->setting = $setting;

        $helptopic = new Help_topic();
        $this->helptopic = $helptopic;

        $slaPlan = new Sla_plan();
        $this->slaPlan = $slaPlan;

        $priority = new Priority();
        $this->priority = $priority;

        $department = new Department();
        $this->department = $department;

        $source = new Ticket_source();
        $this->source = $source;
    }

    /**
     * Resolve the caller's identity from the JWT on this request.
     *
     * $this->user is unusable for this: the constructor above overwrites it
     * with a blank `new User()` right after assigning the authenticated user,
     * so every method that needs the caller re-parses the token itself. This
     * centralises that, since several endpoints below now need the role too.
     *
     * @return \App\User|null
     */
    private function authenticatedApiUser()
    {
        try {
            $user = \JWTAuth::parseToken()->authenticate();

            return $user instanceof User ? $user : null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * Deny the request unless the caller is an agent or admin.
     *
     * Several v1 endpoints (the customer directory, internal notes, API-key
     * rotation) are staff-only by intent but previously relied solely on
     * 'jwt.authOveride' + the shared api_key, neither of which checks role:
     * any customer-role JWT could reach them. This is the shared gate for
     * those endpoints; it returns a 403 JSON response to short-circuit the
     * caller's method, or null when the caller may proceed.
     *
     * @param \App\User|null $user
     *
     * @return \Illuminate\Http\JsonResponse|null
     */
    private function denyUnlessStaff($user)
    {
        if (!$user instanceof User || !in_array($user->role, ['agent', 'admin'], true)) {
            return $this->forbidden();
        }

        return null;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    private function forbidden()
    {
        return response()->json(['error' => Lang::get('lang.unauthorized_access')], 403);
    }

    /**
     * The ticket, if the caller may work on it; null otherwise (or if it doesn't exist).
     *
     * Mirrors Agent\helpdesk\TicketController::authorizeTicketAccess(): an admin
     * may reach any ticket, an agent only one in their own department or
     * assigned to them. A customer never may - the staff-only gate stops them
     * before any ticket endpoint anyway.
     *
     * @param \App\User|null $user
     * @param mixed          $ticketId
     *
     * @return Tickets|null
     */
    private function accessibleTicket($user, $ticketId)
    {
        $ticket = Tickets::where('id', '=', $ticketId)->first();
        if (!$ticket || !$user instanceof User) {
            return null;
        }
        if ($user->role == 'admin') {
            return $ticket;
        }
        if ($user->role == 'agent' && ($ticket->dept_id == $user->primary_dpt || $ticket->assigned_to == $user->id)) {
            return $ticket;
        }

        return null;
    }

    /**
     * Narrow a query over `tickets` to what the caller may see: an agent gets
     * their own department plus tickets assigned to them, an admin everything.
     * Same rule the unassigned/closed/inbox/trash lists already apply. The
     * builder is narrowed in place.
     *
     * @param mixed          $query an Eloquent or query builder over `tickets`
     * @param \App\User|null $user
     *
     * @return void
     */
    private function scopeTicketsToCaller($query, $user)
    {
        if ($user instanceof User && $user->role == 'agent') {
            $query->where(function ($q) use ($user) {
                $q->where('tickets.dept_id', '=', $user->primary_dpt)
                    ->orWhere('tickets.assigned_to', '=', $user->id);
            });
        }
    }

    /**
     * Create Tickets.
     *
     * @method POST
     *
     * @param user_id,subject,body,helptopic,sla,priority,dept
     *
     * @return json
     */
    public function createTicket(\App\Http\Requests\helpdesk\CreateTicketRequest $request, \App\Model\helpdesk\Utility\CountryCode $code)
    {
        try {
            $user_id = $this->request->input('user_id');

            $subject = $this->request->input('subject');
            $body = $this->request->input('body');
            $helptopic = $this->request->input('helptopic');
            $sla = $this->request->input('sla');
            $priority = $this->request->input('priority');
            $header = $this->request->input('cc');
            $dept = $this->request->input('dept');

            $assignto = $this->request->input('assignto');
            $form_data = $this->request->input('form_data');
            $source = $this->request->input('source');
            $attach = $this->request->input('attachments');
            $headers = [];
            if ($header) {
                $headers = explode(',', $header);
            }
            //return $headers;
            /*
             * return s ticket number
             */
            $PhpMailController = new \App\Http\Controllers\Common\PhpMailController();
            $NotificationController = new \App\Http\Controllers\Common\NotificationController();
            $core = new CoreTicketController($PhpMailController, $NotificationController);
            $this->request->merge(['body' => preg_replace('/[ ](?=[^>]*(?:<|$))/', '&nbsp;', nl2br($this->request->get('body')))]);
            $request->replace($this->request->except('token', 'api_key'));
            $request_data = $request->except(['token']);
            $response = $core->post_newticket(new CreateTicketRequest($request->except('token', 'api_key')), $code, true);

            //$response = $this->ticket->createTicket($user_id, $subject, $body, $helptopic, $sla, $priority, $source, $headers, $dept, $assignto, $form_data, $attach);
            //return $response;
            /*
             * return ticket details
             */
            //dd($response);
            //$result = $this->thread->where('id', $response)->first();
            //$result = $this->attach($result->id,$file);
            return response()->json(compact('response'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'))
                            ->header('Authenticate: xBasic realm', 'fake');
        }
    }

    /**
     * Reply for the ticket.
     *
     * @param TicketRequest $request
     *
     * @return json
     */
    public function ticketReply()
    {
        //dd($this->request->all());
        try {
            $v = \Validator::make($this->request->all(), [
                'ticket_id'     => 'required|exists:tickets,id',
                'reply_content' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $attach = $this->request->input('attachments');
            $result = $this->ticket->reply($this->thread, $this->request, $this->attach, $attach);
            $result = $result->join('users', 'ticket_thread.user_id', '=', 'users.id')
                    ->select('ticket_thread.*', 'users.first_name as first_name')
                    ->orderBy('ticket_thread.id', 'desc')
                    ->first();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Edit a ticket.
     *
     * @return json
     */
    public function editTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'ticket_id'       => 'required|exists:tickets,id',
                'subject'         => 'required',
                'sla_plan'        => 'required|exists:sla_plan,id',
                'help_topic'      => 'required|exists:help_topic,id',
                'ticket_source'   => 'required|exists:ticket_source,id',
                'ticket_priority' => 'required|exists:ticket_priority,priority_id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $ticket_id = $this->request->input('ticket_id');
            // ticketEditPost() does no access check of its own
            if (!$this->accessibleTicket($this->authenticatedApiUser(), $ticket_id)) {
                return $this->forbidden();
            }

            return $this->ticket->ticketEditPost($ticket_id, $this->thread, $this->model);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Delete The Ticket.
     *
     * @return json
     */
    public function deleteTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'ticket_id' => 'required|exists:tickets,id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('ticket_id');

            $result = $this->ticket->delete(explode(',', $id), $this->model);

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get all opened tickets.
     *
     * @return json
     */
    public function openedTickets()
    {
        try {
            //            $result = $this->model->where('status', '=', 1)->where('isanswered', '=', 0)->where('assigned_to', '=', null)->orderBy('id', 'DESC')->get();
//            return response()->json(compact('result'));

            $opened = $this->user->join('tickets', function ($join) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->where('isanswered', '=', 0)->where('status', '=', 1)->whereNull('assigned_to');
            });
            $this->scopeTicketsToCaller($opened, $this->authenticatedApiUser());
            $result = $opened
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'title', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->orderBy('ticket_thread.updated_at', 'desc')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toArray();

            return response($result);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get Unsigned Tickets.
     *
     * @return json
     */
    public function unassignedTickets()
    {
        try {
            //dd('sdhjbc');
//            $result = $this->model->where('assigned_to', '=', null)->where('status', '1')->orderBy('id', 'DESC')->get();
//            return response()->json(compact('result'));
            $user = \JWTAuth::parseToken()->authenticate();
            $unassigned = $this->user->join('tickets', function ($join) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->whereNull('assigned_to')->where('status', '=', 1);
            })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->leftJoin('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id');
                    })
                    ->leftJoin('ticket_attachment', 'ticket_attachment.thread_id', '=', 'ticket_thread.id');
            if ($user->role == 'agent') {
                $id = $user->id;
                $dept[] = $user->primary_dpt;
                $unassigned = $unassigned->where(function ($query) use ($dept, $id) {
                    $query->whereIn('tickets.dept_id', $dept)
                            ->orWhere('assigned_to', '=', $id);
                });
            }
            $unassigned = $unassigned->select('ticket_priority.priority_color as priority_color', \DB::raw('substring_index(group_concat(ticket_thread.title order by ticket_thread.id asc) , ",", 1) as title'), 'tickets.duedate as overdue_date', \DB::raw('count(ticket_attachment.id) as attachment'), \DB::raw('max(ticket_thread.updated_at) as updated_at'), 'user_name', 'first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->orderBy('updated_at', 'desc')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toArray();

            return response($unassigned);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get closed Tickets.
     *
     * @return json
     */
    public function closeTickets()
    {
        try {
            //            $result = $this->model->where('status', '>', 1)->where('status', '<', 4)->orderBy('id', 'DESC')->get();
//            return response()->json(compact('result'));
            $user = \JWTAuth::parseToken()->authenticate();
            $result = $this->user->join('tickets', function ($join) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->where('status', '=', 3)->orWhere('status', '=', 2);
            })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->leftJoin('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id');
                    })
                    ->leftJoin('ticket_attachment', 'ticket_attachment.thread_id', '=', 'ticket_thread.id');
            if ($user->role == 'agent') {
                $id = $user->id;
                $dept[] = $user->primary_dpt;
                $result = $result->where(function ($query) use ($dept, $id) {
                    $query->whereIn('tickets.dept_id', $dept)
                            ->orWhere('assigned_to', '=', $id);
                });
            }
            $result = $result->select('tickets.duedate as overdue_date', 'ticket_priority.priority_color as priority_color', \DB::raw('substring_index(group_concat(ticket_thread.title order by ticket_thread.id asc) , ",", 1) as title'), \DB::raw('count(ticket_attachment.id) as attachment'), \DB::raw('max(ticket_thread.updated_at) as updated_at'), 'user_name', 'first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->orderBy('updated_at', 'desc')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toArray();

            return response($result);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get All agents.
     *
     * @return json
     */
    public function getAgents()
    {
        try {
            $result = $this->faveoUser->where('role', 'agent')->orWhere('role', 'admin')->where('active', 1)->get();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get All Teams.
     *
     * @return json
     */
    public function getTeams()
    {
        try {
            $result = $this->team->get();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * To assign a ticket.
     *
     * @return json
     */
    public function assignTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'ticket_id' => 'required',
                'user'      => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('ticket_id');
            $response = $this->ticket->assign($id);
            if ($response == 1) {
                $result = 'success';

                return response()->json(compact('result'));
            } else {
                return response()->json(compact('response'));
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get all customers.
     *
     * @return json
     */
    public function getCustomers()
    {
        try {
            if ($deny = $this->denyUnlessStaff($this->authenticatedApiUser())) {
                return $deny;
            }

            $v = \Validator::make($this->request->all(), [
                'search' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $search = $this->request->input('search');
            $result = $this->faveoUser->where('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%')->orWhere('user_name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->get();

            return response()->json(compact('result'))
                            ->header('X-Header-One', 'Header Value');
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'))
                            ->header('X-Header-One', 'Header Value');
        }
    }

    /**
     * Get all customers having client_id, client_picture, client_name, client_email, client_phone.
     *
     * @return json
     */
    public function getCustomersWith()
    {
        try {
            if ($deny = $this->denyUnlessStaff($this->authenticatedApiUser())) {
                return $deny;
            }

            $users = $this->user
                    ->leftJoin('user_assign_organization', 'user_assign_organization.user_id', '=', 'users.id')
                    ->leftJoin('organization', 'organization.id', '=', 'user_assign_organization.org_id')
                    ->where('users.role', 'user')
                    ->select('users.id', 'users.user_name', 'users.first_name', 'users.last_name', 'users.email', 'users.phone_number', 'users.profile_pic', 'organization.name AS company', 'users.active', 'users.ext as telephone_extension', 'users.mobile', 'users.phone_number as telephone', 'users.country_code as mobile_code')
                    ->paginate(10)
                    ->toJson();

            //dd($users);
            return $users;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'))
                            ->header('Authenticate: xBasic realm', 'fake');
        }
    }

    /**
     * Get a customer by id.
     *
     * @return json
     */
    public function getCustomer()
    {
        try {
            if ($deny = $this->denyUnlessStaff($this->authenticatedApiUser())) {
                return $deny;
            }

            $v = \Validator::make($this->request->all(), [
                'user_id' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('user_id');
            $result = $this->faveoUser->where('id', $id)->where('role', 'user')->first();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Search tickets.
     *
     * @return json
     */
    public function searchTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'search' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $search = $this->request->input('search');
            $matches = $this->thread->select('ticket_thread.ticket_id')
                    ->join('tickets', 'tickets.id', '=', 'ticket_thread.ticket_id')
                    ->where(function ($query) use ($search) {
                        $query->where('ticket_thread.title', 'like', '%'.$search.'%')
                                ->orWhere('ticket_thread.body', 'like', '%'.$search.'%');
                    });
            $this->scopeTicketsToCaller($matches, $this->authenticatedApiUser());
            $result = $matches->get();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get threads of a ticket.
     *
     * @return json
     */
    public function ticketThreads()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'id' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('id');
            // the threads include internal notes
            if (!$this->accessibleTicket($this->authenticatedApiUser(), $id)) {
                return $this->forbidden();
            }
            $result = $this->user
                    ->rightjoin('ticket_thread', 'ticket_thread.user_id', '=', 'users.id')
                    ->select('ticket_thread.id', 'ticket_id', 'user_id', 'poster', 'source', 'title', 'body', 'is_internal', 'format', 'ip_address', 'ticket_thread.created_at', 'ticket_thread.updated_at', 'users.first_name', 'users.last_name', 'users.user_name', 'users.email', 'users.profile_pic')
                    ->where('ticket_id', $id)
                    ->orderBy('ticket_thread.id', 'desc')
                    ->get()
                    ->toJson();

            return $result;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Check the url is valid or not.
     *
     * @return json
     */
    public function checkUrl()
    {
        //dd($this->request);
        try {
            $v = \Validator::make($this->request->all(), [
                'url' => 'required|url',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }

            $url = $this->request->input('url');
            if (!Str::is('*/', $url)) {
                $url = Str::finish($url, '/');
            }

            $url = $url.'/api/v1/helpdesk/check-url?api_key='.$this->request->input('api_key').'&token='.\Config::get('app.token');
            $result = $this->CallGetApi($url);

            //dd($result);
            return response()->json(compact('result'));
        } catch (\Exception $ex) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Success for currect url.
     *
     * @return string
     */
    public function urlResult()
    {
        return 'success';
    }

    /**
     * Call curl function for Get Method.
     *
     * @param type $url
     *
     * @return type int|string|json
     */
    public function callGetApi($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'error:'.curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }

    /**
     * Call curl function for POST Method.
     *
     * @param type $url
     * @param type $data
     *
     * @return type int|string|json
     */
    public function callPostApi($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'error:'.curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }

    /**
     * To generate api string.
     *
     * @return type | json
     */
    public function generateApiKey()
    {
        try {
            // This rotates the one shared api_key that 'api_key_mandatory' checks
            // on every other v1 request. Without a role check here, any
            // customer-role JWT could mint a new one at will, defeating that
            // setting for every other integration using the old key.
            $user = $this->authenticatedApiUser();
            if (!$user instanceof User || $user->role !== 'admin') {
                return response()->json(['error' => Lang::get('lang.unauthorized_access')], 403);
            }

            $set = $this->setting->where('id', '1')->first();
            //dd($set);
            if ($set->api_enable == 1) {
                $key = Str::random(32);
                $set->api_key = $key;
                $set->save();
                $result = $set->api_key;

                return response()->json(compact('result'));
            } else {
                $result = 'please enable api';

                return response()->json(compact('result'));
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get help topics.
     *
     * @return json
     */
    public function getHelpTopic()
    {
        try {
            $result = $this->helptopic->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get Sla plans.
     *
     * @return json
     */
    public function getSlaPlan()
    {
        try {
            $result = $this->slaPlan->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get priorities.
     *
     * @return json
     */
    public function getPriority()
    {
        try {
            $result = $this->priority->select('priority as name', 'priority_id as id')->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get departments.
     *
     * @return json
     */
    public function getDepartment()
    {
        try {
            $result = $this->department->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Getting the tickets.
     *
     * @return type json
     */
    public function getTickets()
    {
        try {
            $query = $this->model->newQuery();
            $this->scopeTicketsToCaller($query, $this->authenticatedApiUser());
            $tickets = $query->orderBy('created_at', 'desc')->paginate(10);
            $tickets->toJson();

            return $tickets;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Fetching the Inbox details.
     *
     * @return type json
     */
    public function inbox()
    {
        try {
            $user = \JWTAuth::parseToken()->authenticate();
            $inbox = $this->user->join('tickets', function ($join) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->where('status', '=', 1);
            })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->leftJoin('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id');
                    })
                    ->leftJoin('ticket_attachment', 'ticket_attachment.thread_id', '=', 'ticket_thread.id')
                    ->where('ticket_status.name', 'Open');
            if ($user->role == 'agent') {
                $id = $user->id;
                $dept[] = $user->primary_dpt;
                $inbox = $inbox->where(function ($query) use ($dept, $id) {
                    $query->whereIn('tickets.dept_id', $dept)
                            ->orWhere('assigned_to', '=', $id);
                });
            }
            $inbox = $inbox->select(\DB::raw('max(ticket_thread.updated_at) as updated_at'), 'user_name', 'first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', \DB::raw('substring_index(group_concat(ticket_thread.title order by ticket_thread.id asc) , ",", 1) as title'), 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'ticket_priority.priority_color as priority_color', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name', 'department.id as department_id', 'users.primary_dpt as user_dpt', \DB::raw('count(ticket_attachment.id) as attachment'), 'tickets.duedate as overdue_date')
                    ->orderBy('updated_at', 'desc')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toArray();

            return response($inbox);
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            $line = $ex->getLine();
            $file = $ex->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Create internal note.
     *
     * @return type json
     */
    public function internalNote()
    {
        try {
            $user = $this->authenticatedApiUser();
            if ($deny = $this->denyUnlessStaff($user)) {
                return $deny;
            }
            if (!$user instanceof User) {
                // Unreachable: denyUnlessStaff() above already rejected a null or
                // non-staff $user. Spelled out so static analysis can narrow $user
                // from App\User|null to App\User for the role/department checks
                // and the note's author below - denyUnlessStaff() returning null
                // doesn't by itself prove that to a separate method call.
                return response()->json(['error' => Lang::get('lang.unauthorized_access')], 403);
            }

            // 'user_id' is intentionally no longer accepted: it let any caller
            // attribute the note to an arbitrary account (including an admin's)
            // by just naming its id. The author is always the authenticated
            // caller now; see $user->id below.
            $v = \Validator::make($this->request->all(), [
                'ticket_id' => 'required|exists:tickets,id',
                'body'      => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $ticketid = $this->request->input('ticket_id');

            if (!$this->accessibleTicket($user, $ticketid)) {
                return $this->forbidden();
            }

            $body = preg_replace('/[ ](?=[^>]*(?:<|$))/', '&nbsp;', nl2br($this->request->input('body')));
            $thread = $this->thread->create(['ticket_id' => $ticketid, 'user_id' => $user->id, 'is_internal' => 1, 'body' => $body]);

            return response()->json(compact('thread'));
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            $line = $ex->getLine();
            $file = $ex->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function getTrash()
    {
        try {
            $user = \JWTAuth::parseToken()->authenticate();
            $trash = $this->user->join('tickets', function ($join) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->where('status', '=', 5);
            })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->leftJoin('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id');
                    })
                    ->leftJoin('ticket_attachment', 'ticket_attachment.thread_id', '=', 'ticket_thread.id');
            if ($user->role == 'agent') {
                $id = $user->id;
                $dept[] = $user->primary_dpt;
                $trash = $trash->where(function ($query) use ($dept, $id) {
                    $query->whereIn('tickets.dept_id', $dept)
                            ->orWhere('assigned_to', '=', $id);
                });
            }
            $trash = $trash->select('ticket_priority.priority_color as priority_color', \DB::raw('substring_index(group_concat(ticket_thread.title order by ticket_thread.id asc) , ",", 1) as title'), 'tickets.duedate as overdue_date', \DB::raw('count(ticket_attachment.id) as attachment'), \DB::raw('max(ticket_thread.updated_at) as updated_at'), 'user_name', 'first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->orderBy('updated_at', 'desc')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toArray();

            return response($trash);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function getMyTicketsAgent()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('user_id');
            // an agent sees their own assignments; only an admin may look at another agent's
            $caller = $this->authenticatedApiUser();
            if (!$caller instanceof User || ($caller->role != 'admin' && $caller->id != $id)) {
                return $this->forbidden();
            }
            if ($this->user->where('id', $id)->first()->role == 'user') {
                $error = 'This user is not an Agent or Admin';

                return response()->json(compact('error'));
            }
            //$user = \JWTAuth::parseToken()->authenticate();
            $result = $this->user->join('tickets', function ($join) {
                $join->on('users.id', '=', 'tickets.assigned_to')
                        ->where('status', '=', 1);
                //->where('user_id', '=', $id);
            })
                    ->join('users as client', 'tickets.user_id', '=', 'client.id')
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->leftJoin('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id');
                    })
                    ->leftJoin('ticket_attachment', 'ticket_attachment.thread_id', '=', 'ticket_thread.id')
                    ->where('users.id', $id)
                    ->select(
                        'ticket_priority.priority_color as priority_color',
                        \DB::raw('substring_index(group_concat(ticket_thread.title order by ticket_thread.id asc) , ",", 1) as title'),
                        'tickets.duedate as overdue_date',
                        \DB::raw('count(ticket_attachment.id) as attachment'),
                        \DB::raw('max(ticket_thread.updated_at) as updated_at'),
                        'client.user_name',
                        'client.first_name',
                        'client.last_name',
                        'client.email',
                        'client.profile_pic',
                        'ticket_number',
                        'tickets.id',
                        'tickets.created_at',
                        'department.name as department_name',
                        'ticket_priority.priority as priotity_name',
                        'sla_plan.name as sla_plan_name',
                        'help_topic.topic as help_topic_name',
                        'ticket_status.name as ticket_status_name'
                    )
                    ->orderBy('updated_at', 'desc')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toArray();

            return response($result);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function getMyTicketsUser()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('user_id');
            // one of the few customer-reachable endpoints: a customer may only
            // list their own tickets, whatever user_id they send
            $caller = $this->authenticatedApiUser();
            if (!$caller instanceof User || ($caller->role == 'user' && $caller->id != $id)) {
                return $this->forbidden();
            }
            if ($this->user->where('id', $id)->first()->role == 'admin' || $this->user->where('id', $id)->first()->role == 'agent') {
                $error = 'This is not a client';

                return response()->json(compact('error'));
            }
            $user = User::where('users.id', $id)
                                            ->leftJoin('user_assign_organization', 'users.id', '=', 'user_assign_organization.user_id')
                                            ->leftJoin('organization', 'user_assign_organization.org_id', '=', 'organization.id')
                                            ->select(
                                                'users.first_name',
                                                'users.last_name',
                                                'users.user_name',
                                                'users.email',
                                                'users.id',
                                                'users.profile_pic',
                                                'users.ban',
                                                'users.active',
                                                'users.is_delete',
                                                'users.phone_number',
                                                'users.ext',
                                                'users.country_code',
                                                'users.mobile',
                                                'organization.name as company'
                                            )->first()->toArray();
            $own = $this->user->join('tickets', function ($join) use ($id) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->where('user_id', '=', $id);
            });
            $this->scopeTicketsToCaller($own, $caller);
            $result = $own
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('ticket_number', 'tickets.id', 'title', 'ticket_status.name as ticket_status_name')
                    ->orderBy('ticket_thread.updated_at', 'desc')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->get()
                    ->toArray();

            return response()->json(['tickets' => $result, 'requester' => $user]);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function getTicketById()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'ticket_id' => 'required|exists:tickets,id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('ticket_id');
            if (!$this->model->where('id', $id)->first()) {
                $error = 'There is no Ticket as ticket id: '.$id;

                return response()->json(compact('error'));
            }
            if (!$this->accessibleTicket($this->authenticatedApiUser(), $id)) {
                return $this->forbidden();
            }
            $query = $this->user->join('tickets', function ($join) use ($id) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->where('tickets.id', '=', $id);
            });

            $response = $this->differenciateHelpTopic($query)
                    ->leftJoin('department', 'tickets.dept_id', '=', 'department.id')
                    ->leftJoin('ticket_priority', 'tickets.priority_id', '=', 'ticket_priority.priority_id')
                    ->leftJoin('ticket_status', 'tickets.status', '=', 'ticket_status.id')
                    ->leftJoin('sla_plan', 'tickets.sla', '=', 'sla_plan.id')
                    ->leftJoin('ticket_source', 'tickets.source', '=', 'ticket_source.id')
                    ->leftJoin('help_topic', 'tickets.help_topic_id', '=', 'help_topic.id');
            //$select = 'users.email','users.user_name','users.first_name','users.last_name','tickets.id','ticket_number','num_sequence','user_id','priority_id','sla','max_open_ticket','captcha','status','lock_by','lock_at','source','isoverdue','reopened','isanswered','is_deleted', 'closed','is_transfer','transfer_at','reopened_at','duedate','closed_at','last_message_at';

            $result = $response->addSelect(
                'users.email',
                'users.user_name',
                'users.first_name',
                'users.last_name',
                'tickets.id',
                'ticket_number',
                'user_id',
                'ticket_priority.priority_id',
                'ticket_priority.priority as priority_name',
                'department.name as dept_name',
                'ticket_status.name as status_name',
                'sla_plan.name as sla_name',
                'ticket_source.name as source_name',
                'sla_plan.id as sla',
                'ticket_status.id as status',
                'lock_by',
                'lock_at',
                'ticket_source.id as source',
                'isoverdue',
                'reopened',
                'isanswered',
                'is_deleted',
                'closed',
                'reopened_at',
                'duedate',
                'closed_at',
                'tickets.created_at',
                'tickets.updated_at',
                'ticket_priority.priority_color as priority_color',
                'help_topic.id as helptopic_id',
                'help_topic.topic as helptopic_name',
                'sla_plan.grace_period as grace_period'
            )->first();
//            $resultticket_source
            $result2 = $result;
            $result = $result->toArray();
            $result['duedate'] = date_add($result2->created_at, date_interval_create_from_date_string($result2->grace_period))->format('Y:m:d H:i:s');
            $result['title'] = utfEncoding(Ticket_Thread::where('ticket_id', $id)->orderBy('id')->first()->title);
            $assigned = Tickets::where('id', $id)->select('assigned_to', 'team_id')->first()->toArray();
            if (!empty($assigned)) {
                if ($assigned['assigned_to'] != null) {
                    $result['assignee'] = User::where('id', $assigned['assigned_to'])->first()->name();
                } elseif ($assigned['team_id'] != null) {
                    $result['assignee'] = Teams::where('id', $assigned['team_id'])->first()->value('name');
                } else {
                    $result['assignee'] = null;
                }
            }

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function createPagination($array, $perPage)
    {
        try {
            //Get current page form url e.g. &page=6
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            //Create a new Laravel collection from the array data
            $collection = new Collection($array);

            //Slice the collection to get the items to display in current page
            $currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();

            //Create our paginator and pass it to the view
            $paginatedResults = new LengthAwarePaginator($currentPageSearchResults, count($collection), $perPage);

            return $paginatedResults;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function collaboratorSearch()
    {
        $this->validate($this->request, ['term' => 'required']);

        try {
            $emails = $this->ticket->autosearch();
            //return $emails;
            $user = new User();
            if (count($emails) > 0) {
                foreach ($emails as $key => $email) {
                    $user_model = $user->where('email', $email)->first();
                    //return $user_model;
                    $users[$key]['name'] = $user_model->first_name.' '.$user_model->last_name;
                    $users[$key]['email'] = $email;
                    $users[$key]['avatar'] = $this->avatarUrl($email);
                }
            }
            //return $users;

            return response()->json(compact('users'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        }
    }

    public function avatarUrl($email)
    {
        try {
            $user = new User();
            $user = $user->where('email', $email)->first();
            if ($user->profile_pic) {
                $url = url('uploads/profilepic/'.$user->profile_pic);
            } else {
                $url = \Gravatar::src($email);
            }

            return $url;
        } catch (\Exception $ex) {
            //return $ex->getMessage();
            throw new \Exception($ex->getMessage());
        }
    }

    public function addCollaboratorForTicket()
    {
        try {
            $v = \Validator::make(
                Input::all(),
                [
                    'email'     => 'required|email|unique:users',
                    'ticket_id' => 'required',
                ]
            );
            if ($v->fails()) {
                $error = $v->messages();

                return response()->json(compact('error'));
            }
            if (!$this->accessibleTicket($this->authenticatedApiUser(), Input::get('ticket_id'))) {
                return $this->forbidden();
            }
            $collaborator = $this->ticket->useradd();

            return response()->json(compact('collaborator'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $ex) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        }
    }

    public function getCollaboratorForTicket()
    {
        try {
            $v = \Validator::make(
                Input::all(),
                [
                    'ticket_id' => 'required',
                ]
            );
            if ($v->fails()) {
                $error = $v->messages();

                return response()->json(compact('error'));
            }
            if (!$this->accessibleTicket($this->authenticatedApiUser(), Input::get('ticket_id'))) {
                return $this->forbidden();
            }
            $collaborator = $this->ticket->getCollaboratorForTicket();

            return response()->json(compact('collaborator'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $ex) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        }
    }

    public function deleteCollaborator()
    {
        try {
            $v = \Validator::make(
                Input::all(),
                [
                    'ticketid' => 'required',
                    'email'    => 'required',
                ]
            );
            if ($v->fails()) {
                $result = $v->messages();

                return response()->json(compact('result'));
            }
            $ticketid = Input::get('ticketid');
            if (!$this->accessibleTicket($this->authenticatedApiUser(), $ticketid)) {
                return $this->forbidden();
            }
            // userremove() deletes the collaborator row named by 'data1' whatever
            // ticket it is on, so make sure it belongs to the ticket just checked
            $collaboratorId = Input::get('data1');
            if ($collaboratorId !== null && !\App\Model\helpdesk\Ticket\Ticket_Collaborator::where('id', '=', $collaboratorId)->where('ticket_id', '=', $ticketid)->exists()) {
                return $this->forbidden();
            }
            $collaborator = $this->ticket->userremove();

            return response()->json(compact('collaborator'));
        } catch (\Exception $ex) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        }
    }

    public function dependency()
    {
        try {
            $user = \JWTAuth::parseToken()->authenticate();
            $tickets = \DB::table('tickets');
            if ($user->role == 'agent') {
                $id = $user->id;
                $dept[] = $user->primary_dpt;
                $tickets = $tickets->whereIn('tickets.dept_id', $dept)->orWhere('assigned_to', '=', $user->id);
            }
            $department = $this->department->select('name', 'id')->get()->toArray();
            $sla = $this->slaPlan->select('name', 'id', 'grace_period as sla_duration')->get()->toArray();
            $staff = $this->user->where('role', 'agent')->select('email', 'id')->get()->toArray();
            $team = $this->team->select('name', 'id')->get()->toArray();
            $priority = \DB::table('ticket_priority')->select('priority', 'priority_id')->get();
            $helptopic = $this->helptopic->select('topic', 'id')->get()->toArray();
            $status = \DB::table('ticket_status')->select('name', 'id')->get();
            $source = \DB::table('ticket_source')->select('name', 'id')->get();
            $statuses = collect($tickets
                            ->join('ticket_status', 'tickets.status', '=', 'ticket_status.id')
                            ->select('ticket_status.name as status', \DB::raw('COUNT(tickets.id) as count'))
                            ->groupBy('ticket_status.id')
                            ->get())->transform(function ($item) {
                                return ['name' => ucfirst($item->status), 'count' => $item->count];
                            });
            $unassigned = $this->user->leftJoin('tickets', function ($join) {
                $join->on('users.id', '=', 'tickets.user_id')
                        ->whereNull('assigned_to')->where('status', '=', 1);
            })
                    ->where(function ($query) use ($user) {
                        if ($user->role != 'admin') {
                            $query->where('tickets.dept_id', '=', $user->primary_dpt);
                        }
                    })
                    ->select(\DB::raw('COUNT(tickets.id) as unassined'))
                    ->value('unassined');
            $mytickets = $this->user->leftJoin('tickets', function ($join) use ($user) {
                $join->on('users.id', '=', 'tickets.assigned_to')
                        ->where('tickets.assigned_to', '=', $user->id)->where('status', '=', 1);
            })
                    ->where(function ($query) use ($user) {
                        if ($user->role != 'admin') {
                            $query->where('tickets.dept_id', '=', $user->primary_dpt)->orWhere('assigned_to', '=', $user->id);
                        }
                    })
                    ->select(\DB::raw('COUNT(tickets.id) as my_ticket'))
                    ->value('my_ticket');
            $depend = collect([['name' => 'unassigned', 'count' => $unassigned], ['name' => 'mytickets', 'count' => $mytickets]]);
            $collection = $statuses->merge($depend);
            $result = ['departments' => $department, 'sla' => $sla, 'staffs' => $staff, 'teams' => $team,
                'priorities'         => $priority, 'helptopics' => $helptopic,
                'status'             => $status,
                'sources'            => $source,
                'tickets_count'      => $collection, ];

            return response()->json(compact('result'));
//            $result     = ['departments' => $department, 'sla'         => $sla, 'staffs'      => $staff, 'teams'       => $team,
//                'priorities'  => $priority, 'helptopics'  => $helptopic, 'status'      => $status, 'sources'     => $source,];

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        }
    }

    public function differenciateHelpTopic($query)
    {
        $ticket = $query->first();
        $check = 'department';
        if ($ticket) {
            if ($ticket->dept_id && $ticket->help_topic_id) {
                return $this->getSystem($check, $query);
            }
            if (!$ticket->dept_id && $ticket->help_topic_id) {
                return $query->select('tickets.help_topic_id');
            }
            if ($ticket->dept_id && !$ticket->help_topic_id) {
                return $query->select('tickets.dept_id');
            }
        }

        return $query;
    }

    public function getSystem($check, $query)
    {
        switch ($check) {
            case 'department':
                return $query->select('tickets.dept_id');
            case 'helpTopic':
                return $query->select('tickets.help_topic_id');
            default:
                return $query->select('tickets.dept_id');
        }
    }

    /**
     * Register a user with username and password.
     *
     * @param Request $request
     *
     * @return type json
     */
    public function register(Request $request)
    {
        try {
            $v = \Validator::make($request->all(), [
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $auth = $this->user;
            $email = $request->input('email');
            $username = $request->input('email');
            $password = \Hash::make($request->input('password'));
            $role = $request->input('role');
            if ($auth->role == 'agent') {
                $role = 'user';
            }
            $user = new User();
            $user->password = $password;
            $user->user_name = $username;
            $user->email = $email;
            $user->role = $role;
            $user->save();

            return response()->json(compact('user'));
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function createUser()
    {
        try {
            $v = \Validator::make(
                $this->request->all(),
                [
                    'username'   => 'required|unique:users,user_name',
                    'first_name' => 'required',
                ]
            );
            if ($v->fails()) {
                $error = $v->messages();

                return response()->json(compact('error'));
            }
            $str = Str::random(8);
            $array = ['password' => $str, 'password_confirmation' => $str, 'email' => $this->request->input('email'), 'full_name' => $this->request->input('first_name')];
            $all = $this->request->input();
            $merged = $array + $all;
            $request = new \App\Http\Requests\helpdesk\RegisterRequest();
            $request->replace($merged);
            if ($request->has('username')) {
                $request->merge(['user_name' => $request->get('username')]);
            }
            \Route::dispatch($request);
            $auth = new \App\Http\Controllers\Auth\AuthController();
            $user = new User();
            $register = $auth->postRegister($user, $request, true);
            if ($register) {
                return response()->json(compact('register'));
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        }
    }
}
