<?php


namespace App\Services\Activity;

use App\Exceptions\RestfulException;
use App\Models\InvitationRecordModel;
use App\Models\InvitationRelationModel;
use App\Models\UserAssetsModel;
use App\Supports\Constant\ActivityConst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
    public function createInvitation($userId, $superiorId)
    {
        //检查是否绑定
        $exist = InvitationRelationModel::query()->where('user_id', $userId)->exists();
        if ($exist){
            throw new RestfulException('该用户已经绑定了其他用户');
        }

        InvitationRelationModel::query()->insert([
            'user_id' => $userId,
            'superior_id' => $superiorId,
        ]);
        $redis = Redis::connection('activity');
        $level = $redis->hget('invitation_level', $superiorId) ?: 0;
        $nc = ActivityConst::ACTIVITY_INVITATION_LEVEL[$level + 1];
        $count = InvitationRelationModel::query()->where('superior_id', $superiorId)->where('is_active', 1)->count();
        if ($count >= $nc) {
            //等级增加
            $redis->hset('invitation_level', $superiorId, $level + 1);
        }

        return true;
    }

    /**
     * 获取指定用户的邀请关系.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserInvitation($userId)
    {
        return InvitationRelationModel::query()->macroWhere(['user_id', $userId])->macroSelect(["*"])->macroFirst();
    }

    /**
     * 获得绿豆
     *
     * @param $userId
     * @param $orderNo
     * @param $money
     * @return bool
     */
    public function getBean($userId, $orderNo, $money)
    {
        //计算绿豆
        $level = Redis::connection('activity')->hget('invitation_level', $userId) ?: 0;
        $multi = ActivityConst::ACTIVITY_BEAN_MULTI[$level];
        $bean = round($money, $multi);
        DB::beginTransaction();
        try {
            //生成记录
            InvitationRecordModel::query()->insert([
                'user_id' => $userId,
                'order_no' => $orderNo,
                'bean' => $bean,
                'status' => 2
            ]);

            //更新绿豆
            $assert = UserAssetsModel::query()->where('user_id', $userId)->macroFirst();
            $bean += $assert['bean'];
            UserAssetsModel::query()->where('user_id', $userId)->update(['bean' => $bean]);
        } catch (\Throwable $e) {
            Log::channel('activity')->warn(['msg' => '绿豆增加失败', 'order' => $orderNo]);
            DB::rollBack();
        }

        DB::commit();

        return true;
    }

    /**
     * 花费绿豆
     *
     * @param $userId
     * @param $orderNo
     * @param $bean
     */
    public function costBean($userId, $orderNo, $bean)
    {
        DB::beginTransaction();
        try {
            //查询绿豆数量
            $assert = $assert = UserAssetsModel::query()->where('user_id', $userId)->macroFirst();
            if ($assert['bean'] < $bean) {
                throw new RestfulException('绿豆数量不足');
            }

            $nb = bcsub($assert['bean'], $bean);
            UserAssetsModel::query()->where('user_id', $userId)->update(['bean' => $nb]);

            //生成记录
            InvitationRecordModel::query()->insert([
                'user_id' => $userId,
                'order_no' => $orderNo,
                'bean' => -$bean,
                'status' => 1
            ]);

        } catch (\Throwable $e){
            Log::channel('activity')->warn( ['msg' => "{$orderNo}绿豆花费失败", 'data' => $e->getMessage()]);
            DB::rollBack();
        }

        DB::commit();
    }

    /**
     * 绿豆消费成功
     *
     * @param $orderNo
     * @return int
     */
    public function consumeBean($orderNo)
    {
        return InvitationRecordModel::query()->where('order_no', $orderNo)->update(['status'=>2]);
    }

    public function getBeanRecord($userId, $type, $page, $pageSize)
    {
        //1 是 获得 2是消费
        if ($type = 1) {
            $where = ['user_id'=> $userId, ['bean', '>', 0]];
            return InvitationRecordModel::query()->macroQuery($where, ['*'], [], $page, $pageSize, 1);
        }else{

        }
    }
}
