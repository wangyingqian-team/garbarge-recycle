<?php

namespace App\Console\Commands;

use App\Supports\Constant\RedisKeyConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 自动清理每天的回收订单预约数量
 */
class AutoClearRecycleOrderCountToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recycle:order_count_auto_clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天的回收订单预约数量自动清理';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $redis = Redis::connection('recycle');
        $reservedOrderCountKey = RedisKeyConst::RECYCLE_RESERVED_ORDER_COUNT_TODAY;
        $redis->del($reservedOrderCountKey);

        Log::channel('recycle')->info('每天的回收订单预约数量自动清理完成');

        return true;
    }
}