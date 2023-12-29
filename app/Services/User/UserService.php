<?php

namespace App\Services\User;

use App\Events\UserRegisterEvent;
use App\Models\InvitationRelationModel;
use App\Models\UserAddressModel;
use App\Models\UserAssetsModel;
use App\Models\UserModel;
use App\Services\Activity\InvitationService;
use App\Services\Activity\NewerService;
use App\Supports\Constant\RedisKeyConst;
use App\Supports\Constant\UserConst;
use Illuminate\Support\Facades\Redis;

class UserService
{

    public function create($data)
    {
        //基本信息
        $val = [
            'openid' => $data['openid'],
            'nickname' => $data['nickname'],
            'sex' => $data['sex'],
            'avatar' => $data['headimgurl'],
            'code' => strtoupper(md5($data['openid'])),
            'mobile' => $data['mobile'] ?? '',
        ];

        $userId = UserModel::query()->insertGetId($val);

        //初始化资产
        UserAssetsModel::query()->insert(['user_id' => $userId]);

        //统计今日新增人数
        Redis::connection('user')->incr(RedisKeyConst::TODAY_NEWER);

        //添加新人标志
        app(NewerService::class)->newer($userId);

        //用户注册事件
        //event(new UserRegisterEvent($userId));

        return $userId;
    }


    public function update($data)
    {
        $val = [
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['headimgurl'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'sex' => $data['sex'] ?? '',
        ];

        return UserModel::query()->where('id', $data['user_id'])->update($val);
    }

    public function getUserBasicInfo($userId)
    {
        return UserModel::query()->whereKey($userId)->macroFirst();
    }


    public function getUserDetail($param)
    {
        $where = is_int($param) ?  ['id' => $param] : ['openid' => $param];
        $userInfo = UserModel::query()->where($where)->macroFirst();
        //等级进度
        $userInfo['exp_progress'] = $userInfo['level'] == 9 ? 1 : round($userInfo['exp'] / UserConst::LEVEL_EXP[$userInfo['level'] + 1]);
        //地址
        $userInfo['default_address'] = UserAddressModel::query()->where(['user_id'=> $userInfo['id'],'is_default'=> 1])->macroFirst();
        //资产
        $userInfo['asserts'] = UserAssetsModel::query()->where('user_id',  $userInfo['id'])->macroFirst();
        //会员权益
        $equity = $this->getUserEquity( $userInfo['id']);
        $userInfo['equity'] = $equity;
        //今日是否签到
        $userInfo['is_sign'] = !empty(Redis::connection('user')->hget(RedisKeyConst::USER_SIGN,  $userInfo['id']));

        /** @var InvitationService $invitationService */
        $invitationService = app(InvitationService::class);
        //我的上级
        $userInfo['superior'] = $invitationService->getUserInvitation($userInfo['id']);
        //今日是否已经免费抽奖
        $redis = Redis::connection('activity');
        $userInfo['free_chou'] = !(bool)$redis->hexists('chou_jiang_count', $userInfo['id']);

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
            //送积分
            $jifen = 10;
            app(AssertService::class)->increaseJifen($userId, $jifen);
            $redis->hset(RedisKeyConst::USER_SIGN, $userId, true);
        }

        return true;
    }

    //会员经验增加
    public function increaseExp($uid, $exp)
    {
        $userInfo = UserModel::query()->where('user_id', $uid)->macroFirst();
        $nExp = $userInfo['exp'] + $exp;
        $nLevel = $userInfo['level'];
        if ($nLevel < UserConst::LEVEL_MAX && $nExp >= UserConst::LEVEL_EXP[$nLevel + 1]) {
            $nLevel += 1;
        }

        UserModel::query()->where('user_id', $uid)->update(['exp' => $nExp, 'level' => $nLevel]);

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
        $userInfo = UserModel::query()->where('id', $userId)->macroFirst();
        $level = $userInfo['level'];
        $equity = [];
        foreach (UserConst::LEVEL_EQUITY as $k => $v) {
            $equity[$k] = $v[$level];
        }

        //获取免费抽奖次数
        $redis = Redis::connection('activity');
        $equity['free_chou'] = !(bool)$redis->hget('chou_jiang', $userId);

        return $equity;
    }
}
