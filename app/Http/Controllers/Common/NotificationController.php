<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Notification\Notification;
use App\Model\helpdesk\Notification\NotificationType;
use App\Model\helpdesk\Notification\UserNotification;
use App\Model\helpdesk\Ticket\Tickets;
use App\User;

class NotificationController extends Controller
{
    /**
     * ********************************************
     * Class Notification Controller.
     * ********************************************
     * This controller is used to generate in app notification
     * under the folling occurrence
     * 1. Ticket Creation
     * 2. Ticket Reply
     * 3. User Creation.
     *
     * @author      Ladybird <info@ladybirdweb.com>
     */
    public $user;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $user = new User();
        $this->user = $user;
        // checking authentication
        $this->middleware('auth');
        // checking if role is agent
        $this->middleware('role.agent');
    }

    /**
     * This function is used to create in app notifications.
     *
     * @param type $model_id
     * @param type $userid_created
     * @param type $type_id
     * @param type $forwhome
     */
    public function create($model_id, $userid_created, $type_id, $forwhome = [])
    {
        try {
            if (empty($forwhome)) {
                $ticket = Tickets::where('id', '=', $model_id)->first();
                $forwhome = $this->user->where('role', '=', 'agent')->where('primary_dpt', '=', $ticket->dept_id)->get();
                $forwhome2 = $this->user->where('role', '=', 'admin')->get();
                $forwhome = $forwhome->merge($forwhome2);
            }
            // system notification
            $notify = Notification::create(['model_id' => $model_id, 'userid_created' => $userid_created, 'type_id' => $type_id]);
            foreach ($forwhome as $agent) {
                $type_message = NotificationType::where('id', '=', $type_id)->first();
                UserNotification::create(['notification_id' => $notify->id, 'user_id' => $agent['id'], 'is_read' => 0]);
                // $this->PushNotificationController->response($agent->fcm_token, $type_message->message . $model_id, $model_id);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * This function is to mark all ticket to read status.
     *
     * @param type $id
     *
     * @return int
     */
    public function markAllRead($id)
    {
        // Single bulk UPDATE instead of loading every row and saving it one by one
        // (that was one query per notification — very slow with many unread). Eloquent
        // still stamps updated_at automatically, so the result is identical.
        UserNotification::where('user_id', '=', \Auth::user()->id)
            ->where('is_read', '=', '0')
            ->update(['is_read' => '1']);

        // explicit JSON so the client can update the unread badge from the response.
        // everything is read now, so the unread count is 0.
        return response()->json(['status' => 'success', 'unread' => 0]);
    }

    /**
     * This function to mark read.
     *
     * @param type $id
     *
     * @return int
     */
    public function markRead($id)
    {
        $markasread = UserNotification::where('notification_id', '=', $id)->where('user_id', '=', \Auth::user()->id)->where('is_read', '=', '0')->get();
        foreach ($markasread as $mark) {
            $mark->is_read = '1';
            $mark->save();
        }

        return 1;
    }

    /**
     * function to show all the notifications.
     *
     * @return type
     */
    public function show()
    {
        // skip notifications whose type or ticket was deleted, so every page is full and nothing renders half-empty
        $notificationList = $this->getNotifications()
            ->whereHas('notification', function ($query) {
                $query->whereHas('type')->where(function ($query) {
                    $query->whereHas('model')->orWhereHas('type', function ($query) {
                        $query->where('type', 'registration');
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications-all', compact('notificationList'));
    }

    /**
     * function to delete notifications.
     *
     * @param type $id
     *
     * @return int
     */
    public function delete($id)
    {
        $markasread = UserNotification::where('notification_id', '=', $id)->where('user_id', '=', \Auth::user()->id)->get();
        foreach ($markasread as $mark) {
            $mark->delete();
        }

        return 1;
    }

    public function deleteAll()
    {
        try {
            $notifications = new Notification();
            if ($notifications->count() > 0) {
                foreach ($notifications->get() as $notification) {
                    $notification->delete();
                }
            }
            $notifications->dummyDelete();

            return redirect()->back()->with('success', \Lang::get('lang.all_notifications_deleted'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * get the page to list the notifications.
     *
     * @return response
     */
    public static function getNotifications()
    {
        $notifications = UserNotification::with([
            'notification.type' => function ($query) {
                $query->select('id', 'message', 'type');
            }, 'users' => function ($query) {
                $query->select('id', 'email', 'profile_pic');
            }, 'notification.model' => function ($query) {
                $query->select('id', 'ticket_number');
            },
        ])->where('user_id', '=', \Auth::user()->id);

        return $notifications;
    }
}
