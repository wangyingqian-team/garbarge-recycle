<?php

namespace App\Services\Achievement;

use Illuminate\Support\Facades\Redis;

/**
 * 成就系统.
 */
class AchieveService
{
    protected $redis;

    public function __construct()
    {
        $this->redis = Redis::connection('user');
    }
}
