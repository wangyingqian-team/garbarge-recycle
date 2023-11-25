<?php

namespace App\Console\Commands;

use App\Models\CouponRecordModel;
use App\Services\User\AssertService;
use App\Supports\Constant\UserConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;

/**
* 项目初始化数据填充
*/
class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '项目初始化数据填充';

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
        //售卖通知机器人10个
        $robots = [
            [
                'nickname' => '浅蓝色',
                'recycle_amount' => 68.5
            ],
            [
                'nickname' => '纯洁的半烟',
                'recycle_amount' => 37.4
            ],
            [
                'nickname' => '喵呜大人',
                'recycle_amount' => 28.8
            ],
            [
                'nickname' => '十里温柔',
                'recycle_amount' => 57.2
            ],
            [
                'nickname' => '张望的时',
                'recycle_amount' => 18.9
            ],
            [
                'nickname' => '贫僧爱泡妞',
                'recycle_amount' => 55.1
            ],
            [
                'nickname' => '粉圆鲜奶',
                'recycle_amount' => 47.1
            ],
            [
                'nickname' => '帆布鞋也比',
                'recycle_amount' => 38.7
            ],
            [
                'nickname' => '天空之城',
                'recycle_amount' => 21.8
            ],
            [
                'nickname' => '被猪拱的白菜',
                'recycle_amount' => 19.5
            ],
        ];

        $redis=  Redis::connection('common');
        foreach ($robots as $robot) {
            $redis->hset('recycle_amount_notify',$robot['nickname'], $robot['recycle_amount']);
        }

    }
}
