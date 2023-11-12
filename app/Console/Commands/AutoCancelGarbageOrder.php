<?php
namespace App\Console\Commands;

use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Supports\Constant\GarbageRecycleConst;
use Illuminate\Console\Command;

class AutoCancelGarbageOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:recycle_timeout_cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '回收员上门超时订单自动取消';

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
        // 查询过期没有上门的订单
        $timeoutOrderList = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderList([
            'appoint_end_time|>' => date("Y-m-d H:i:s"),
            'status|in' => [GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED, GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED]
        ], ['*'], ['id' => 'asc'], 1, -1, false);

        // 自动超时取消订单
        foreach ($timeoutOrderList as $timeoutOrder) {
            app(GarbageRecycleOrderService::class)->cancelRecycleOrderBySystem($timeoutOrder['order_no']);
        }

        return true;
    }
}
