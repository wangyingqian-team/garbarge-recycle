<?php

namespace App\Console\Commands;

use App\Models\CouponRecordModel;
use App\Services\User\AssertService;
use App\Supports\Constant\UserConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

/**
* 月底结算没有增加的信用值
*/
class SettleCredit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:settle_credit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '月底结算没有增加的信用值';

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
        //todo  获取当月售卖垃圾的金额
        $orders = [
            1 => 100,
            2 => 30,
        ];
        $credits = UserConst::CREDIT_CHANGE['month_settle'];
        /** @var AssertService $assertService */
        $assertService = get_service(AssertService::class);
        foreach ($orders as $id => $c) {
            foreach ($credits as $credit) {
                if ($c >= $credit['amount']) {
                    $assertService->increaseCredit($id, $credit['number']);
                    break;
                }
            }
        }

        return true;
    }
}
