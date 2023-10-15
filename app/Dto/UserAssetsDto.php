<?php

namespace App\Dto;

class UserAssetsDto extends Dto
{
    public function initUserAssets($userId) {
        return $this->query->insert(['user_id'=>$userId]);
    }

    public function getUserAssetsByUserId($userId){
        return $this->query->where('user_id', $userId)->macroFirst();
    }

    public function changeJifen($userId, $jifen) {
        return $this->query->where('user_id', $userId)->update(['jifen'=>$jifen]);
    }

    public function changeBean($userId, $bean) {
        return $this->query->where('user_id', $userId)->update(['bean'=>$bean]);
    }

    public function addThrowTotal($userId, $throwTotal) {
        return $this->query->where('user_id', $userId)->update(['throw_total'=>$throwTotal]);
    }

    public function addRecycleTotal($userId, $throwTotal) {
        return $this->query->where('user_id', $userId)->update(['recycle_total'=>$throwTotal]);
    }

    public function addRecycleAmount($userId, $amount) {
        return $this->query->where('user_id', $userId)->update(['recycle_amount'=>$amount]);
    }
}
