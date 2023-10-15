<?php

namespace App\Console\Commands;

use App\Supports\Constant\RedisKeyConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 自动清理每天的代仍订单预约数量
 */
class AutoClearThrowOrderCountToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'throw:order_count_auto_clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天的代仍订单预约数量自动清理';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $redis = Redis::connection('throw');
        $reservedOrderCountKey = RedisKeyConst::THROW_RESERVED_ORDER_COUNT_TODAY;
        $redis->del($reservedOrderCountKey);

        Log::channel('throw')->info('每天的代仍订单预约数量自动清理完成');

        return true;
    }
}