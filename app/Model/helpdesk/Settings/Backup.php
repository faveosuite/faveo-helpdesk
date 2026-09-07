<?php

namespace App\Model\helpdesk\Settings;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $table = 'backups';

    protected $fillable = ['filename', 'db_name', 'file_path', 'db_path', 'version'];
}
