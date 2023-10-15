<?php

namespace App\Console\Commands;

use App\Services\Coupon\ThrowCouponService;
use App\Supports\Constant\RedisKeyConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class InitTodayNewer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:init_today_newer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Redis::connection('user')->del(RedisKeyConst::TODAY_NEWER);

        return true;
    }
}
