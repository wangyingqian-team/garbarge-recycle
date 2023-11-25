<?php

namespace App\Services\User;

use App\Models\UserAssetsModel;
use App\Models\UserModel;
use App\Supports\Constant\UserConst;

/**
 * 用户资产
 *
 * Class AssertService
 * @package App\Services\User
 */
class AssertService
{
    //增加积分
    public function increaseJifen($userId, $jifen)
    {
        $userInfo = UserModel::query()->where('id', $userId)->macroFirst();
        $je = UserConst::LEVEL_EQUITY['ji_fen_extra'][$userInfo['level']];
        $j = round($jifen * $je);
        $jifen += $j;
        $assert = UserAssetsModel::query()->where('id', $userId)->macroFirst();
        $jifen += $assert['jifen'];
        UserAssetsModel::query()->where('id', $userId)->update(['jifen' => $jifen]);
    }

    //扣减积分
    public function decreaseJifen($userId, $jifen)
    {
        $assert = UserAssetsModel::query()->where('id', $userId)->macroFirst();
        $assert['jifen'] -= $jifen ;
        UserAssetsModel::query()->where('id', $userId)->update(['jifen' => $jifen]);
        return true;
    }

    //增加信用值
    public function increaseCredit($userId, $credit) {
        $assert = UserAssetsModel::query()->where('id', $userId)->macroFirst();
        $credit += $assert['credit'];
        UserAssetsModel::query()->where('id', $userId)->update(['credit' => $credit]);
        return true;
    }

    //扣减信用值
    public function decreaseCredit($userId, $credit) {
        $assert = UserAssetsModel::query()->where('id', $userId)->macroFirst();
        $assert['credit'] -= $credit;
        UserAssetsModel::query()->where('id', $userId)->update(['credit' => $credit]);
        return true;
    }

    //前10 售卖金额
    public function getRecycleAmountLimit10()
    {
      //  $data = UserAssetsModel::query()->where('','>', 10)->select()->limit(10)->get()->toArray();

        $data = UserAssetsModel::query()->macroQuery(['recycle_amount|>' => 10], ['id','user_id','recycle_amount','userInfo.*' ],['recycle_amount'=>'desc'],0,10);

        return $data;
    }
}
