<?php

namespace App\Services\User;

use App\Dto\UserActivityLogDto;
use App\Dto\UserAddressDto;
use App\Dto\UserAssetsDto;
use App\Dto\UserDto;
use App\Dto\UserInvitationDto;
use App\Dto\UserSignLogDto;
use App\Events\UserRegisterEvent;
use App\Exceptions\RestfulException;
use App\Models\UserAddressModel;
use App\Models\UserAssetsModel;
use App\Models\UserModel;
use App\Services\Common\ConfigService;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\ConfigConst;
use App\Supports\Constant\RedisKeyConst;
use App\Supports\Constant\UserConst;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UserService
{

    public function create($data)
    {
        //todo 多加一个邀请码
        $val = [
            'openid' => $data['openid'],
            'nickname' => $data['nickname'],
            'sex' => $data['sex'],
            'avatar' => $data['headimgurl'],
        ];

        $userId = UserModel::query()->insertGetId($val);

        //初始化资产
        UserAssetsModel::query()->insert(['user_id'=>$userId]);

        //统计今日新增人数
        Redis::connection('user')->incr(RedisKeyConst::TODAY_NEWER);

        //用户注册事件
        event(new UserRegisterEvent($userId));

        return $userId;
    }


    public function update($data)
    {

    }

    public function getUserList($where, $select = ['*'], $orderBy = [], $page = 0, $limit= 0, $withPage = true) {
        return app(UserDto::class)->getUserList($where, $select, $orderBy, $page, $limit, $withPage);
    }


    public function getUserDetail($userId)
    {
        $userInfo = UserModel::query()->where('id', $userId)->macroFirst();
        //地址

        //资产
        $userInfo['asserts'] = app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
        $userInfo['asserts']['today_income'] = Redis::connection('user')->hget(
            RedisKeyConst::TODAY_USER_INCOME,
            $userId
        ) ?: '0.00';

        //会员权益

        //上级userid

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
        }
        return true;
    }

    /**
     * @param $userId
     * @param $data
     * @return bool
     * @throws \Throwable
     */
    public function createAddress($userId, $data)
    {

        DB::transaction(
            function () use ($userId, $data) {

                if ($data['is_default'] ?? false) {
                    $oldDefault = UserAddressModel::query()->where(['user_id'=> $userId, 'is_default'=>UserConst::IS_DEFAULT_ADDRESS])->macroFirst();
                    if (!empty($oldDefault)) {
                        UserAddressModel::query()->whereKey($oldDefault['id'])->update(['is_default' =>UserConst::IS_NOT_DEFAULT_ADDRESS]);
                    }
                }

                $iData = [
                    'user_id' => $userId,
                    'village_id' => $data['village_id'],
                    'village_floor_id' => $data['village_floor_id'],
                    'address' => $data['address'],
                    'mobile' => $data['mobile'],
                    'is_default' => $data['is_default'],
                ];
                 UserAddressModel::query()->insert($iData);
            }
        );

        return true;
    }


    /**
     * @param $id
     * @param $data
     * @return mixed
     * @throws \Throwable
     */
    public function updateAddress($id, $data)
    {
        DB::transaction(
            function () use ($id, $data) {

                if ($data['is_default'] ?? false) {
                    $oldDefault = UserAddressModel::query()->where(['user_id'=> $id, 'is_default'=>UserConst::IS_DEFAULT_ADDRESS])->macroFirst();
                    if (!empty($oldDefault)) {
                        UserAddressModel::query()->whereKey($oldDefault['id'])->update(['is_default' =>UserConst::IS_NOT_DEFAULT_ADDRESS]);
                    }
                }

                $iData = [
                    'village_id' => $data['village_id'] ?? null,
                    'village_floor_id' => $data['village_floor_id'] ?? null,
                    'address' => $data['address'] ??null,
                    'mobile' => $data['mobile'] ?? null,
                    'is_default' => $data['is_default'] ?? null,
                ];

                $iData = array_null($iData);
                if (!empty($iData)) {
                    UserAddressModel::query()->whereKey($id)->update($iData);
                }
            });

        return $id;
    }

    //会员经验增加
    public function increaseExp($uid, $exp)
    {
        $userInfo = UserModel::query()->where('user_id', $uid)->macroFirst();
        $nExp = $userInfo['exp'] + $exp;
        $nLevel = $userInfo['level'];
        if ($nLevel < UserConst::LEVEL_MAX && $nExp >= UserConst::LEVEL_EXP[$nLevel + 1]) {
            $nLevel +=1;
        }

        UserModel::query()->where('user_id', $uid)->update(['exp'=> $nExp, 'level'=>$nLevel]);

        return true;
    }

    /**
     * 获取会员权益
     *
     * @param $userId
     * @return array
     */
    public function getUserEquity($userId)
    {
        $userInfo = UserModel::query()->where('user_id', $userId)->macroFirst();
        $level = $userInfo['level'];
        $equity = [];
        foreach (UserConst::LEVEL_EQUITY as $k => $v) {
            $equity[$k] = $v[$level];
        }

        return $equity;
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
