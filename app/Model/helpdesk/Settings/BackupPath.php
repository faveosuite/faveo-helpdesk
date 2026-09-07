<?php

namespace App\Model\helpdesk\Settings;

use Illuminate\Database\Eloquent\Model;

class BackupPath extends Model
{
    protected $table = 'backup_paths';

    protected $fillable = ['backup_path'];
}
