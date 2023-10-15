<?php

namespace App\Services\User;

use App\Dto\RecyclerAssetsDto;
use App\Supports\Constant\RecyclerConst;

class RecyclerAssetsService
{
    /**
     * 获取回收员资产
     *
     * @param $recyclerId
     *
     * @return mixed
     */
    public function getRecycleAssets($recyclerId)
    {
        return app(RecyclerAssetsDto::class)->getAssets($recyclerId);
    }

    /**
     * 增加回收资产
     *
     * @param $recyclerId
     * @param int $num
     * @return bool
     */
    public function addRecycleTotal($recyclerId, $num = 1) {
        return app(RecyclerAssetsDto::class)->changeAssets($recyclerId, RecyclerConst::ASSETS_RECYCLE, $num);
    }

    /**
     * 增加支出资产
     *
     * @param $recyclerId
     * @param int $num
     * @return bool
     */
    public function addAmountTotal($recyclerId, $amount) {
        return app(RecyclerAssetsDto::class)->changeAssets($recyclerId, RecyclerConst::ASSETS_AMOUNT, $amount);
    }


}
