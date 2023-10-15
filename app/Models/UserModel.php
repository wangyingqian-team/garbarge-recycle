<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'user';

    public $timestamps = false;

    public function assets() {
        return $this->hasOne(UserAssetsModel::class, 'user_id');
    }
}
