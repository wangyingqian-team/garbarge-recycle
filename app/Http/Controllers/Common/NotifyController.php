<?php

namespace App\Http\Controllers\Common;

use App\Exceptions\RestfulException;
use App\Http\Controllers\Controller;
use App\Services\Common\AliOssService;
use App\Services\Common\ConfigService;
use App\Services\User\AssertService;
use App\Supports\Constant\ConfigConst;
use App\Supports\Constant\ImageTypeConst;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

class NotifyController extends Controller
{
    /**
     * 公告
     *
     * @return mixed
     */
    public function announcement()
    {
        $data = [
            'type' => '',
            'data' => []
        ];
        //获取公告
        /** @var ConfigService $configService */
        $configService = app(ConfigService::class);
        $announcement = $configService->getConfig(ConfigConst::USER_ANNOUNCEMENT);
        if (!empty($announcement)) {
            $data['data'] = $announcement;
            $data['type'] = 'sys';
        }else{
            $data['type'] = 'user';
            //获取最近用户收益
            /** @var AssertService $assertService */
            $assertService = app(AssertService::class);
            $recycleAmount = $assertService->getRecycleAmountLimit10();
            foreach ($recycleAmount as $item) {
                $data['data'][] = [
                    'nickname' => $item['user_info']['nickname'],
                    'recycle_amount' => $item['recycle_amount']
                ];
            }
            $count = count($recycleAmount);
            $c = 10 - $count;
            if ($c > 0) {
                $robots = Redis::connection('common')->hgetall('recycle_amount_notify');
                foreach ($robots as $k=>$v){
                    $data['data'][] = [
                        'nickname' => $k,
                        'recycle_amount' => $v
                    ];
                    if (count($data['data']) == 10) {
                        break;
                    }
                }

            }
        }

        return  $data;
    }


    //发送短信
    public function sendSms() {}
}
