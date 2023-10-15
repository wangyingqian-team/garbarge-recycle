<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarbageSiteModel extends Model
{
    protected $table = 'garbage_site';
    public $timestamps = false;

    public function admin() {
        return $this->belongsTo(AdminModel::class, 'admin_id');
    }
}
