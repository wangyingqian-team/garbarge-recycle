<?php

namespace App\Console;

use App\Console\Commands\AutoCheckInvitationActive;
use App\Console\Commands\AutoClearSign;
use App\Console\Commands\CouponExpire;
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
        AutoClearSign::class,
        AutoCheckInvitationActive::class,
        CouponExpire::class,
        SettleCredit::class,
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

        $schedule->command('garbage:coupon_expire')->dailyAt('21:00');

        $schedule->command('garbage:clear_sign')->dailyAt('23:59');

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
