<?php

namespace App\Model\helpdesk\Utility;

use App\BaseModel;

class AttemptLock extends BaseModel
{
    protected $table = 'attempt_locks';

    protected $fillable = ['context', 'identifier', 'count', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];
}