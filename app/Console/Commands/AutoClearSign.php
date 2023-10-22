<?php

namespace App\Console\Commands;

use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Supports\Constant\GarbageRecycleConst;
use App\Supports\Constant\RedisKeyConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
* 签到每天重置
*/
class AutoClearSign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:clear_sign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '签到每天重置';

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
        $redis = Redis::connection('user');
        $redis->del(RedisKeyConst::USER_SIGN);

        Log::channel('user')->info('签到重置成功！');

        return true;
    }
}
