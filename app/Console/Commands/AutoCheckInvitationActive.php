<?php

namespace App\Console\Commands;

use App\Models\InvitationRecordModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
* 签到每天重置
*/
class AutoCheckInvitationActive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:invitation_active';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每天检查邀请关系状态';

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
        $ids = [];
        $superiorIds = InvitationRecordModel::query()->groupBy(['superior_id'])->get()->toArray();

        foreach ($superiorIds as $id => $userIds) {
            //查询这些用户最近3个月订单 todo
            $uids = [];
            //过滤掉有订单的
            $ids = array_merge($ids, array_diff($userIds, $uids));
        }

        //全部变成未激活
        InvitationRecordModel::query()->whereIn('user_id', $ids)->update(['is_active' => 2]);

        Log::channel('user')->info('邀请关系状态变更成功！');

        return true;
    }
}
