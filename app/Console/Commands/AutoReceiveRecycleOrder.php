<?php

namespace App\Console\Commands;

use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Supports\Constant\GarbageRecycleConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 回收订单自动接单定时任务（每小时执行一次）
 */
class AutoReceiveRecycleOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recycle:order_auto_receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '回收订单自动接单';

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
        // 查询未来一个小时已预约回收的订单（回收时间在未来一个小时）.
        $startTime = date("Y-m-d H:00:00", strtotime("+1 hour"));
        $endTime = date("Y-m-d H:00:00", strtotime("+2 hour"));
        $where = [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED,
            'recycling_start_time|>=' => $startTime,
            'recycling_start_time|<' => $endTime
        ];
        $select = ['*'];
        $orderBy = ['create_time' => 'asc'];
        $page = $pageSize = 0;
        $receivingOrderList = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderList($where, $select, $orderBy, $page, $pageSize);

        // 对这些订单进行自动接单.
        if (!empty($receivingOrderList)) {
            foreach ($receivingOrderList as $receivingOrder) {
                $orderNo = $receivingOrder['order_no'];
                app(GarbageRecycleOrderService::class)->receiveGarbageRecycleOrder($orderNo);
            }
        }

        Log::channel('recycle')->info('回收订单自动接单处理完成');

        return true;
    }
}