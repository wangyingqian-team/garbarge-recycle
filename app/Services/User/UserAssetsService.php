<?php

namespace App\Services\User;

use App\Dto\UserAssetsDto;
use App\Exceptions\RestfulException;

class UserAssetsService
{
    /**
     * 获取用户资产
     *
     * @param $userId
     *
     * @return mixed
     */
    public function getUserAssets($userId)
    {
        return app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
    }

    /**
     * 更新用户积分
     *
     * @param $userId
     * @param $num
     * @param bool $add
     * @return int
     */
    public function changeUserJifen($userId, $num, $add = true)
    {
        $assets = app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
        $jifen = $add ? $assets['jifen'] + $num : $assets['jifen'] - $num;
        if ($jifen < 0) {
            throw new RestfulException('积分不足');
        }
        return app(UserAssetsDto::class)->changeJifen($userId, $jifen);
    }

    /**
     * 更新用户绿豆
     *
     * @param $userId
     * @param $num
     * @param bool $add
     * @return int
     */
    public function changeUserBean($userId, $num, $add = true)
    {
        $assets = app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
        $bean = $add ? $assets['bean'] + $num : $assets['bean'] - $num;
        if ($bean < 0) {
            throw new RestfulException('碳粒不足');
        }
        return app(UserAssetsDto::class)->changeBean($userId, $bean);
    }

    /**
     * 增加代扔单总数
     *
     * @param $userId
     * @return int
     */
    public function addUserThrowTotal($userId)
    {
        $assets = app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
        $throwCount = $assets['throw_total'] + 1;
        return app(UserAssetsDto::class)->addThrowTotal($userId, $throwCount);
    }

    /**
     * 增加回收单总数
     *
     * @param $userId
     * @return int
     */
    public function addUserRecycleTotal($userId)
    {
        $assets = app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
        $throwCount = $assets['recycle_total'] + 1;
        return app(UserAssetsDto::class)->addRecycleTotal($userId, $throwCount);
    }

    /**
     * 增加回收总收入
     *
     * @param $userId
     * @param $amount
     * @return int
     */
    public function addUserRecycleAmount($userId, $amount)
    {
        $assets = app(UserAssetsDto::class)->getUserAssetsByUserId($userId);
        $amount = bcadd($assets['recycle_amount'], $amount);
        return app(UserAssetsDto::class)->addThrowTotal($userId, $amount);
    }
}
