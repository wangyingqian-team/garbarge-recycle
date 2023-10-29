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
use App\Models\InvitationRelationModel;
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

        //用户注册事件
        event(new UserRegisterEvent($userId));

        return $userId;
    }


    public function update($data)
    {
        $val = [
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['headimgurl'] ?? '',
            'mobile' => $data['mobile'] ?? '',
        ];

        return UserModel::query()->where('user_id', $data['user_id'])->update($val);
    }


    public function getUserDetail($userId)
    {
        $userInfo = UserModel::query()->where('id', $userId)->macroFirst();
        //等级进度
        $userInfo['exp_progress'] = $userInfo['level'] == 9 ? 1 : round($userInfo['exp'] / UserConst::LEVEL_EXP[$userInfo['level'] + 1]);
        //地址
        $userInfo['address'] = UserAddressModel::query()->where('user_id', $userId)->orderByDesc('is_default')->get()->toArray();
        //资产
        $userInfo['asserts'] = UserAssetsModel::query()->where('user_id', $userId)->macroFirst();
        //会员权益
        $equity = $this->getUserEquity($userId);
        $userInfo['equity'] = $equity;
        //上级userid
        $superior = InvitationRelationModel::query()->where('user_id', $userId)->where('is_active', 1)->macroFirst();
        $userInfo['superior_id'] = $superior['superior_id'] ?? 0;

        //今日是否签到
        $userInfo['is_sign'] = !empty(Redis::connection('user')->hget(RedisKeyConst::USER_SIGN, $userId));

        return $userInfo;
    }

    public function getUserList($where, $select = ['*'], $orderBy = [], $page = 0, $limit = 0, $withPage = true)
    {
        return app(UserDto::class)->getUserList($where, $select, $orderBy, $page, $limit, $withPage);
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
            $this->increaseJifen($userId, $jifen);
            $redis->hset(RedisKeyConst::USER_SIGN, $userId, true);
        }

        return true;
    }

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
        $jifen -= $assert['jifen'];
        UserAssetsModel::query()->where('id', $userId)->update(['jifen' => $jifen]);
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
                    $oldDefault = UserAddressModel::query()->where(['user_id' => $userId, 'is_default' => UserConst::IS_DEFAULT_ADDRESS])->macroFirst();
                    if (!empty($oldDefault)) {
                        UserAddressModel::query()->whereKey($oldDefault['id'])->update(['is_default' => UserConst::IS_NOT_DEFAULT_ADDRESS]);
                    }
                }

                $iData = [
                    'user_id' => $userId,
                    'village_id' => $data['village_id'],
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
                    $oldDefault = UserAddressModel::query()->where(['user_id' => $id, 'is_default' => UserConst::IS_DEFAULT_ADDRESS])->macroFirst();
                    if (!empty($oldDefault)) {
                        UserAddressModel::query()->whereKey($oldDefault['id'])->update(['is_default' => UserConst::IS_NOT_DEFAULT_ADDRESS]);
                    }
                }

                $iData = [
                    'village_id' => $data['village_id'] ?? null,
                    'address' => $data['address'] ?? null,
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

    //删除地址
    public function delAddress($id) {
        UserAddressModel::query()->whereKey($id)->delete();
    }

    //地址列表
    public function getAddressList($userId) {
        return UserAddressModel::query()->where('user_id', $userId)->get()->toArray();
    }

    //地址列表
    public function getAddressDetail($id) {
        return UserAddressModel::query()->whereKey($id)->macroFirst();
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
