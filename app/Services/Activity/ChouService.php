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
    const CHOU_SECOND = 2592000; //获取的券保持30天

    //每次抽奖花费的积分
    const COST_JI_FEN = 100;



    //概率和奖品   优惠券30天有效
    const JIANG_PIN = [//概率 => 奖品  10%不中奖
        //积分
        'ji_fen' => [ //80%
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
     * @param int $jifen
     *
     */
    public function chou($userId,  $jifen = 0)
    {
        $r = [
            'is_hit' => false,
            'prize' => [
                'type' => '',
                'num' => ''
            ],
        ];

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
            /** @var CouponService $couponService */
            $couponService = get_service(CouponService::class);
            DB::beginTransaction();
            try {
                $t = Carbon::today()->addDays(30)->timestamp;
                $r1= mt_rand(1, 100);
                $r2 = mt_rand(1, 100);
                if ($r1 <= 80) {
                    foreach (self::JIANG_PIN['ji_fen'] as $k => $v) {
                        if ($k >= $r2) {
                            $jifen += $v;
                            $r['prize'] = [
                                'type' => 'ji_fen',
                                'num' => $v
                            ];
                            break;
                        }
                    }
                    UserAssetsModel::query()->where('user_id', $userId)->update(['jifen' => $jifen]);
                }elseif ($r1 <= 85) {
                    foreach (self::JIANG_PIN['hua_fei'] as $k => $v) {
                        if ($k >= $r2) {
                            $couponService->obtainCoupon($userId, $v, $t);
                            $r['prize'] = [
                                'type' => 'hua_fei',
                                'num' => $v
                            ];
                            break;
                        }
                    }
                }elseif($r1 <=90){
                    foreach (self::JIANG_PIN['peng_zhang'] as $k => $v) {
                        if ($k >= $r2) {
                            $r['prize'] = [
                                'type' => 'peng_zhang',
                                'num' => $v
                            ];
                            $couponService->obtainCoupon($userId, $v, $t);
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

        return $r;
    }
}
