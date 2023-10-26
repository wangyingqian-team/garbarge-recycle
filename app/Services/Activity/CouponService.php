<?php


namespace App\Services\Activity;

use App\Exceptions\RestfulException;
use App\Models\CouponRecordModel;
use App\Models\InvitationRecordModel;
use App\Models\InvitationRelationModel;
use App\Models\UserAssetsModel;
use App\Models\UserModel;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\UserConst;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 抽奖活动
 *
 * Class InvitationService
 * @package App\Services\Activity
 */
class CouponService
{
    //每次抽奖花费的积分
    const COST_JI_FEN = 100;



    //概率和奖品   优惠券30天有效
    const JIANG_PIN = [//概率 => 奖品
        //积分
        'ji_fen' => [ //90%
            5 => 10,
            10 => 20,
            20 => 30,
            30 => 50,
            50 => 80,
            70 => 100,
            80 => 200,
            90 => 300,
            95 => 500,
            98 => 800,
            100 => 1000
        ],
        //话费  概率 => 奖品id
        'hua_fei' => [ // 5%
            85 => 1, //10元话费券
            95 => 2, //20元话费券
            97 => 3, //30元话费券
            99 => 4, //50元话费券
            100 => 5, //100元话费券
        ],
        //膨胀券
        'peng_zhang' => [ // 5%
            80 => 6, // 1.1倍膨胀, 最高膨胀10元
            90 => 7, // 1.2倍膨胀， 最高膨胀20元
            95 => 8, // 1.3倍膨胀，最高膨胀30元
            98 => 9,// 1.4倍膨胀，最高膨胀40元
            100 => 10// 1.5倍膨胀，最高膨胀50元
        ],
    ];

    /**
     * 抽奖
     *
     * @param $userId
     * @param $superiorId
     * @return bool
     */
    public function chou($userId,  $jifen = 0)
    {
        //redis 记录抽奖次数
        $redis = Redis::connection('activity');
        $b = (bool)$redis->hget('chou_jiang', $userId);

        if ($b) {
            //花积分抽奖
            if ($jifen <= 0 ) {
                throw new RestfulException('抽奖异常，请稍后再试！');
            }

            $userAssert = UserAssetsModel::query()->where('user_id', $userId)->macroFirst();
            if ($jifen > $userAssert['jifen']) {
                throw new RestfulException('积分不足！');
            }

            $jifen = bcsub($userAssert['jifen'], $jifen);

            DB::beginTransaction();
            try {
                $t = Carbon::today()->addDays(30)->timestamp;
                $r1= mt_rand(1, 100);
                $r2 = mt_rand(1, 100);
                if ($r1 <= 90) {
                    foreach (self::JIANG_PIN['ji_fen'] as $k => $v) {
                        if ($k >= $r2) {
                            $jifen += $v;
                            break;
                        }
                    }
                    UserAssetsModel::query()->where('user_id', $userId)->update(['jifen' => $jifen]);
                }elseif ($r1 <= 95) {
                    foreach (self::JIANG_PIN['hua_fei'] as $k => $v) {
                        if ($k >= $r2) {
                            CouponRecordModel::query()->insert([
                                'user_id' => $userId,
                                'coupon_id' => $v,
                                'expire_time' => $t,
                            ]);
                            break;
                        }
                    }
                }else{
                    foreach (self::JIANG_PIN['peng_zhang'] as $k => $v) {
                        if ($k >= $r2) {
                            CouponRecordModel::query()->insert([
                                'user_id' => $userId,
                                'coupon_id' => $v,
                                'expire_time' => $t,
                            ]);
                            break;
                        }
                    }
                }

            }catch (\Throwable $e) {
                DB::rollBack();;
            }

            DB::commit();
        }

        $redis->hincrby('chou_jiang', $userId, 1);

        return true;
    }

    /**
     * 优惠券详情
     *
     * @param $id
     * @return mixed
     */
    public function getCouponDetail($id)
    {
        return CouponRecordModel::query()->whereKey($id)->macroFirst();
    }

    /**
     * 优惠券列表
     *
     * @param $userId
     * @param $type
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getCouponList($userId, $type, $page, $limit)
    {
        $where = [
            'user_id' => $userId,
            'type' => $type
        ];
        return CouponRecordModel::query()->macroQuery($where, ['*'], ['create_time'=>'desc'], $page, $limit, true);
    }

    /**
     * 使用话费券
     *
     * @param $userId
     * @param $id
     * @param $orderOn
     * @return bool
     */
    public function useHuaFeiCoupon($userId, $id, $orderOn)
    {
        $coupon = CouponRecordModel::query()->where(['user_id' => $userId, 'id' => $id, 'status' => 1])->macroFirst();
        if (empty($coupon)){
            throw new RestfulException('话费券使用异常，请稍后再试！');
        }

        CouponRecordModel::query()->where('id', $id)->update(['status' => 2, 'order_no' => $orderOn, 'used_time' => time()]);

        return true;
    }

    /**
     * 使用膨胀券
     *
     * @param $userId
     * @param $id
     * @param $orderOn
     * @return bool
     */
    public function usePengZhangCoupon($userId, $id, $orderOn, $money)
    {
        $coupon = CouponRecordModel::query()->where(['user_id' => $userId, 'id' => $id, 'status' => 1])->macroFirst();
        if (empty($coupon)){
            throw new RestfulException('膨胀券使用异常，请稍后再试！');
        }

        CouponRecordModel::query()->where('id', $id)->update(['status' => 2, 'order_no' => $orderOn, 'used_time'=>time()]);
        $t = [
            6 => 1.1,
            7 => 1.2,
            8 => 1.3,
            9 => 1.4,
            10 => 1.5
        ];

        return bcmul($t[$coupon['coupon_id']], $money);
    }

    /**
     * 核销优惠券
     *
     * @param $id
     * @return int
     */
    public function verifyCoupon($id)
    {
        return CouponRecordModel::query()->whereKey($id)->update(['status' => 3]);
    }
}
