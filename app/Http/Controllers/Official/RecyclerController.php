<?php

namespace App\Http\Controllers\Official;

use App\Services\Common\ConfigService;
use App\Supports\Constant\ConfigConst;

class RecyclerController extends BaseController
{
    public function index()
    {
        $data['banner'] = app(ConfigService::class)->getConfig(ConfigConst::RECYCLE_BANNER);
        $data['announcement'] = app(ConfigService::class)->getConfig(ConfigConst::RECYCLE_ANNOUNCEMENT);

        ##TODO 获取回收订单信息

    }
}
