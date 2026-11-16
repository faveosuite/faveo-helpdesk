<?php

namespace App\Model\helpdesk\Notification;

use App\BaseModel;

class UserNotification extends BaseModel
{
    protected $table = 'user_notification';

    protected $fillable = [

        'notification_id', 'user_id', 'is_read',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Notification, $this>
     */
    public function notification()
    {
        $related = \App\Model\helpdesk\Notification\Notification::class;
        $id = 'notification_id';

        return $this->belongsTo($related, $id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function users()
    {
        $related = \App\User::class;
        $id = 'user_id';

        return $this->belongsTo($related, $id);
    }

//    public function delete() {
//        //$this->notification()->delete();
//        parent::delete();
//    }
}
