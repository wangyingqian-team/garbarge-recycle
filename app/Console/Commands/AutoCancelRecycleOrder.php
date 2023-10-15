<?php

namespace App\Console\Commands;

use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Supports\Constant\GarbageRecycleConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
* 回收订单自动取消定时任务（系统取消）
*/
class AutoCancelRecycleOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recycle:order_auto_cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '回收订单自动取消';

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
        // 查询过去一个小时已预约代仍的订单（代仍时间在过去一个小时）.
        $startTime = date("Y-m-d H:00:00", strtotime("-1 hour"));
        $endTime = date("Y-m-d H:00:00", time());
        $where = [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED,
            'recycling_end_time|>=' => $startTime,
            'recycling_end_time|<' => $endTime
        ];
        $select = ['*'];
        $orderBy = ['create_time' => 'asc'];
        $page = $pageSize = 0;
        $cancelingOrderList = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderList($where, $select, $orderBy, $page, $pageSize);

        // 对这些订单进行自动取消.
        if (!empty($cancelingOrderList)) {
            foreach ($cancelingOrderList as $cancelingOrder) {
                $orderNo = $cancelingOrder['order_no'];
                app(GarbageRecycleOrderService::class)->cancelRecycleOrderBySystem($orderNo);
            }
        }

        Log::channel('recycle')->info('回收订单自动取消处理完成');

        return true;
    }
}