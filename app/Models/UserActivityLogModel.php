<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLogModel extends Model
{
    protected $table = 'user_activity_log';
    public $timestamps = false;
}
