<?php


namespace App\Services\Activity;

use App\Dto\ActivityInvitationDto;
use App\Models\InvitationRelationModel;
use App\Supports\Constant\ActivityConst;

/**
 * 邀请有礼活动
 *
 * Class InvitationService
 * @package App\Services\Activity
 */
class InvitationService
{
    /**
     * 绑定邀请关系
     *
     * @param $userId
     * @param $superiorId
     * @return bool
     */
    public function createInvitation($userId, $superiorId){
        return InvitationRelationModel::query()->insert([
            'user_id' => $userId,
            'superior_id' => $superiorId,
            'level' => ActivityConst::ACTIVITY_INVITATION_DEFAULT_LEVEL,
            'exp' => 0
        ]);
    }

    /**
     * 邀请关系升级
     *
     * @param $userId
     * @param $money
     * @return int
     */
    public function updateInvitationExp($userId, $money) {
        $invitation = InvitationRelationModel::query()->where('user_id', $userId)->macroFirst();
        $exp = $money * 100 + $invitation['exp'];
        $level = $invitation['level'];

        if ($level < count(ActivityConst::ACTIVITY_INVITATION_LEVEL) && $exp >= ActivityConst::ACTIVITY_INVITATION_LEVEL[$level + 1]){
            $level +=1;
        }
        //todo 增加绿豆

        return InvitationRelationModel::query()->where('user_id', $userId)->update(['exp' => $exp, 'level' => $level]);
    }

    /**
     * 获取下级列表
     *
     * @param $superiorId
     * @return array
     */
    public function getInvitationListBySuperiorId($superiorId) {
        return InvitationRelationModel::query()->where('superior_id', $superiorId)->get()->toArray() ?? [];
    }

    /**
     * 获取绿豆账单详情
     *
     * @param $id
     * @return mixed
     */
    public function getBeanBillDetail($id) {

    }

    /**
     * 获取绿豆账单列表
     *
     * @param $superiorId
     * @return mixed
     */
    public function getBeanBillListBySuperiorId($superiorId) {

    }

}
