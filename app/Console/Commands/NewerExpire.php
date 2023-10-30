<?php

namespace App\Console\Commands;

use App\Models\CouponRecordModel;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

/**
* 新人身份自动过期
*/
class NewerExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:newer_expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '新人身份自动过期';

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
        $t = time();
        $ids = [];
        $r = Redis::connection('activity');
        $newers = $r->hgetall('newer_identity');
        foreach ($newers as $id => $time) {
            if ($time <= $t){
                array_push($ids, $id);
            }
        }

        if (!empty($ids)) {
           $r->hdel('newer_identity', $ids);
        }

        return true;
    }
}
