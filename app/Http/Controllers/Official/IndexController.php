<?php

namespace App\Http\Controllers\Official;

use App\Services\Common\ConfigService;
use App\Services\JifenShop\JifenItemService;
use App\Supports\Constant\ConfigConst;

class IndexController extends BaseController
{

    /**
     * 首页
     *
     * @return mixed
     */
    public function index()
    {
        $data['banner'] = app(ConfigService::class)->getConfig(ConfigConst::USER_BANNER);
        $data['announcement'] = app(ConfigService::class)->getConfig(ConfigConst::USER_ANNOUNCEMENT);
        $data['garbage_types'] = [];
        $data['jifen_items'] = app(JifenItemService::class)->getAppointList(6);
        $data['order_no'] = generate_order_no('R');
        $data['7dates'] = date('Y-m-d', strtotime('+7 day'));

        return $this->success($data);
    }

}
