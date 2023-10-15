<?php

namespace App\Services\User;

use App\Dto\UserActivityLogDto;
use App\Dto\UserAssetsDto;
use App\Dto\UserDto;
use App\Dto\UserInvitationDto;
use App\Dto\UserSignLogDto;
use App\Events\UserRegisterEvent;
use App\Exceptions\RestfulException;
use App\Services\Common\ConfigService;
use App\Services\Coupon\ThrowCouponService;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\ConfigConst;
use App\Supports\Constant\CouponConst;
use App\Supports\Constant\RedisKeyConst;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Redis;

class UserService
{

    public function create($data)
    {
        $userId = app(UserDto::class)->create($data);

        //初始化资产
        app(UserAssetsDto::class)->initUserAssets($userId);

        //设置新人时间

        $this->setNewerEndTime($userId);

        //统计今日新增人数
        Redis::connection('user')->incr(RedisKeyConst::TODAY_NEWER);

        //用户注册事件
        event(new UserRegisterEvent($userId));

        return $userId;
    }


    public function getUserList($where, $select = ['*'], $orderBy = [], $page = 0, $limit= 0, $withPage = true) {
        return app(UserDto::class)->getUserList($where, $select, $orderBy, $page, $limit, $withPage);
    }

    public function getUserBaseInfo($userId)
    {
        return app(UserDto::class)->getUserByUserId($userId);
    }

    public function getUserDetail($userId)
    {
        $userInfo = app(UserDto::class)->getUserByUserId($userId);
        //资产
        $userInfo['asserts'] = app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
        $userInfo['asserts']['today_income'] = Redis::connection('user')->hget(
            RedisKeyConst::TODAY_USER_INCOME,
            $userId
        ) ?: '0.00';
        //今日是否签到
        $userInfo['is_sign'] = !empty(Redis::connection('user')->hget(RedisKeyConst::USER_SIGN, $userId));

        return $userInfo;
    }

    /**
     * 签到
     *
     * @param $userId
     * @return bool
     */
    public function sign($userId)
    {
        $redis = Redis::connection('user');
        $sign = $redis->hget(RedisKeyConst::USER_SIGN, $userId);
        if (empty($sign)) {
            app(UserSignLogDto::class)->sign($userId);
            $redis->hset(RedisKeyConst::USER_SIGN, $userId, true);
            //发一张代扔券
            app(ThrowCouponService::class)->createCoupon($userId, CouponConst::THROW_ORIGIN_ONE);
        }
        return true;
    }


    public function setNewerEndTime($userId)
    {
        $day = app(ConfigService::class)->getConfig(ConfigConst::NEWER_CONTINUE_DAYS);
        $time = Carbon::now()->addDays($day)->timestamp;
        return app(UserDto::class)->updateNewerEndTimeByUserId($userId, $time);
    }

    /**
     * 领取活动奖励
     *
     * @param $userId
     * @param $activityId
     * @param $type
     * @return bool
     */
    public function completeActivity($userId, $activityId, $type, $reward)
    {
        $exist = app(UserActivityLogDto::class)->hasReceived($userId, $activityId, $type);

        if (!$exist) {
            DB::transaction(function () use($userId, $type, $activityId, $reward) {
                app(UserActivityLogDto::class)->create($userId, $activityId, $type);

                if ($type == ActivityConst::ACTIVITY_INVITE) {
                    app(UserAssetsService::class)->changeUserBean($userId, $reward);
                }

                if ($type == ActivityConst::ACTIVITY_NEWER) {
                    app(UserAssetsService::class)->changeUserJifen($userId, $reward);
                }
            });
        }else {
            throw new RestfulException('已经领取过了');
        }

        return true;
    }

    /**
     * 邀请新人记录
     *
     * @param $inviterId
     * @param $userId
     * @return bool
     */
    public function inviteNewer($inviterId, $userId)
    {
        return app(UserInvitationDto::class)->create($inviterId, $userId);
    }

    /**
     * 获取邀请新用户的数量
     *
     * @param $userId
     * @return int
     */
    public function getUserInvitationCount($userId)
    {
        return app(UserInvitationDto::class)->getTotalByUserId($userId);
    }

    /**
     * 获取用户领取活动奖励记录
     *
     * @param $userId
     * @return array
     */
    public function getActivityLog($userId,$type) {
        return app(UserActivityLogDto::class)->getListByUserId($userId,$type);
    }
}
