<?php

namespace App\Console\Commands;

use App\Services\GarbageThrow\GarbageThrowOrderService;
use App\Supports\Constant\GarbageThrowConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 代仍订单自动接单定时任务（每小时执行一次）
 */
class AutoReceiveThrowOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'throw:order_auto_receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '代仍订单自动接单';

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
        // 查询未来一个小时已预约代仍的订单（代仍时间在未来一个小时）.
        $startTime = date("Y-m-d H:00:00", strtotime("+1 hour"));
        $endTime = date("Y-m-d H:00:00", strtotime("+2 hour"));
        $where = [
            'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RESERVED,
            'throwing_start_time|>=' => $startTime,
            'throwing_start_time|<' => $endTime
        ];
        $select = ['*'];
        $orderBy = ['create_time' => 'asc'];
        $page = $pageSize = 0;
        $receivingOrderList = app(GarbageThrowOrderService::class)->getGarbageThrowOrderList($where, $select, $orderBy, $page, $pageSize);

        // 对这些订单进行自动接单.
        if (!empty($receivingOrderList)) {
            foreach ($receivingOrderList as $receivingOrder) {
                $orderNo = $receivingOrder['order_no'];
                app(GarbageThrowOrderService::class)->receiveGarbageThrowOrder($orderNo);
            }
        }

        Log::channel('throw')->info('代仍订单自动接单处理完成');

        return true;
    }
}