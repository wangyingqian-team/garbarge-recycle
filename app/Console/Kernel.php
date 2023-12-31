<?php

namespace App\Console;

use App\Console\Commands\AutoAddRecycleNotice;
use App\Console\Commands\AutoCancelGarbageOrder;
use App\Console\Commands\AutoCheckInvitationActive;
use App\Console\Commands\AutoClearChou;
use App\Console\Commands\AutoClearSign;
use App\Console\Commands\CouponExpire;
use App\Console\Commands\CouponExpireSoon;
use App\Console\Commands\Init;
use App\Console\Commands\NewerExpire;
use App\Console\Commands\SettleCredit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Init::class,
        AutoClearSign::class,
        AutoCheckInvitationActive::class,
        CouponExpireSoon::class,
        CouponExpire::class,
        SettleCredit::class,
        NewerExpire::class,
        AutoAddRecycleNotice::class,
        AutoCancelGarbageOrder::class,
        AutoClearSign::class,
        AutoClearChou::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('garbage:settle_credit')->monthlyOn(1,'2:10');

        $schedule->command('garbage:invitation_active')->dailyAt('3:00');

        $schedule->command('garbage:coupon_expire_soon')->dailyAt('6:00');

        $schedule->command('garbage:coupon_expire')->dailyAt('21:00');

        $schedule->command('garbage:newer_expire')->dailyAt('22:00');

        $schedule->command('garbage:clear_sign')->dailyAt('23:59');

        $schedule->command('garbage:clear_chou')->dailyAt('00:01');

        $schedule->command('garbage:user_notice_add')->cron('0 9-18 * * *');

        $schedule->command('garbage:recycle_timeout_cancel')->cron('0 * * * *');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
