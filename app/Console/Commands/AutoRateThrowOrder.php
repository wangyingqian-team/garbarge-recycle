<?php

namespace App\Console\Commands;

use App\Services\Common\ConfigService;
use App\Services\GarbageThrow\GarbageThrowOrderService;
use App\Services\GarbageThrow\GarbageThrowRateService;
use App\Supports\Constant\ConfigConst;
use App\Supports\Constant\GarbageThrowConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 代仍订单自动好评
 */
class AutoRateThrowOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'throw:order_auto_rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '代仍订单自动好评';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 查询超时自动好评的天数
        $autoRateDays = app(ConfigService::class)->getConfig(ConfigConst::AUTO_RATE_DAYS);

        // 查询当前已经超过{$autoRateDays}天没有评价的代仍订单.
        $countEndTime = date("Y-m-d H:i:s", strtotime("-{$autoRateDays} day"));
        $where = [
            'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_FINISHED,
            'finish_time|<=' => $countEndTime
        ];
        $select = ['*'];
        $orderBy = ['create_time' => 'asc'];
        $page = $pageSize = 0;
        $ratingOrderList = app(GarbageThrowOrderService::class)->getGarbageThrowOrderList($where, $select, $orderBy, $page, $pageSize);

        // 对这些订单进行自动好评.
        if (!empty($ratingOrderList)) {
            $type = GarbageThrowConst::GARBAGE_THROW_RATE_TYPE_GOOD;
            $content = '好评';
            $image = '';

            foreach ($ratingOrderList as $ratingOrder) {
                $userId = $ratingOrder['user_id'];
                $orderNo = $ratingOrder['order_no'];
                app(GarbageThrowRateService::class)->addGarbageThrowRate($userId, $orderNo, $type, $content, $image);
            }
        }

        Log::channel('throw')->info('代仍订单自动好评处理完成');

        return true;
    }
}