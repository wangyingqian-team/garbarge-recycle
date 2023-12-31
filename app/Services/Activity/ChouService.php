<?php


namespace App\Services\Activity;

use App\Exceptions\RestfulException;
use App\Models\CouponRecordModel;
use App\Models\InvitationRecordModel;
use App\Models\InvitationRelationModel;
use App\Models\UserAssetsModel;
use App\Supports\Constant\ActivityConst;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 新人福利活动
 *
 * Class InvitationService
 * @package App\Services\Activity
 */
class ChouService
{
    //概率和奖品   优惠券30天有效
    const JIANG_PIN = [//概率 => 奖品  10%不中奖
        //积分
        'ji_fen' => [ //65%
            60 => 10,
            80 => 20,
            92 => 50,
            100 => 200,
        ],
        //话费  概率 => 奖品id
        'hua_fei' => [ // 0.3%
            100 => [
                'num' => '一张20元话费券',
                'id' => 3,
            ], //10元话费券
        ],
        //膨胀券
        'peng_zhang' => [ // 33%
            70 => [
                'num' => '一张1.1倍膨胀, 最高可膨胀10元',
                'id' => 6,
            ] , // 1.1倍膨胀, 最高膨胀10元
            100 => [
                'num' => '一张1.2倍膨胀， 最高膨胀20元',
                'id' => 7,
            ] ,
        ],
        //代金券
        'dai_jin' => [ // 1.7%
            100 =>  [
                'num' => '一张5元代金券',
                'id' => 13,
            ],// 5元代金券
        ],
    ];


    /**
     * 抽奖
     *
     * @param $userId
     *
     */
    public function chou($userId)
    {
        //每次花费20积分
        $jifen = 20;
        $r = [
            'is_hit' => true,
            'prize' => [
                'type' => '',
                'num' => ''
            ],
        ];

        //redis 记录抽奖次数
        $redis = Redis::connection('activity');

        //每天限制20次抽奖
        $c = $redis->hget('chou_jiang_count', $userId);
        if (empty($c)) {
            $jifen = 0;
        }
        if ($c >= 20) {
            throw new RestfulException('今日抽奖已经达到限定了，请明天再来碰碰运气~~');
        }

        $userAssert = UserAssetsModel::query()->where('user_id', $userId)->macroFirst();
        if ($jifen > $userAssert['jifen']) {
            throw new RestfulException('积分不足！');
        }

        $jifen = bcsub($userAssert['jifen'], $jifen);
        /** @var CouponService $couponService */
        $couponService = get_service(CouponService::class);
        DB::beginTransaction();
        try {
            $t = Carbon::today()->addDays(90);
            $r1 = mt_rand(1, 1000);
            $r2 = mt_rand(1, 100);

            //抽到积分
            if ($r1 <= 650) {
                foreach (self::JIANG_PIN['ji_fen'] as $k => $v) {
                    if ($k >= $r2) {
                        $jifen += $v;
                        $r['prize'] = [
                            'type' => '积分',
                            'num' => $v
                        ];
                        break;
                    }
                }
                //抽到优惠券
            } else{
                if ($r1 <= 653) {
                    foreach (self::JIANG_PIN['hua_fei'] as $k => $v) {
                        if ($k >= $r2) {
                            $id = $v['id'];
                            $type = '话费券';
                            $num = $v['num'];
                            break;
                        }
                    }
                } elseif ($r1 <= 983) {
                    foreach (self::JIANG_PIN['peng_zhang'] as $k => $v) {
                        if ($k >= $r2) {
                            $id = $v['id'];
                            $type = '膨胀券';
                            $num = $v['num'];
                            break;
                        }
                    }
                } elseif ($r1 <= 1000) {
                    foreach (self::JIANG_PIN['dai_jin'] as $k => $v) {
                        if ($k >= $r2) {
                            $id = $v['id'];
                            $type = '代金券';
                            $num = $v['num'];
                            break;
                        }
                    }
                }

                $r['prize'] = [
                    'type' => $type,
                    'num' => $num
                ];
                $couponService->obtainCoupon($userId, $id, $t);
            }


            //更新积分
            UserAssetsModel::query()->where('user_id', $userId)->update(['jifen' => $jifen]);

        } catch (\Throwable $e) {
            DB::rollBack();;
        }


        DB::commit();


        $redis->hincrby('chou_jiang_count', $userId, 1);

        return $r;
    }
}
