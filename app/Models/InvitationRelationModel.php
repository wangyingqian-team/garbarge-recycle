<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationRelationModel extends Model
{
    protected $table = 'invitation_relation';
    public $timestamps = false;

    //上级信息
    public function supUserInfo()
    {
        return $this->belongsTo(UserModel::class, 'superior_id', 'id');
    }

    //下级信息
    public function subUserInfo()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }
}
