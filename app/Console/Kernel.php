<?php

namespace App\Console;

use App\Console\Commands\AutoCancelThrowOrder;
use App\Console\Commands\AutoClearThrowOrderCountToday;
use App\Console\Commands\AutoRateThrowOrder;
use App\Console\Commands\AutoReceiveThrowOrder;
use App\Console\Commands\ExpireThrowCoupon;
use App\Console\Commands\InitTodayNewer;
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
        ExpireThrowCoupon::class,
        InitTodayNewer::class,
        AutoReceiveThrowOrder::class,
        AutoCancelThrowOrder::class,
        AutoRateThrowOrder::class,
        AutoClearThrowOrderCountToday::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('coupon:expire_throw_coupon')->dailyAt('23:59');

        $schedule->command('user:init_today_newer')->daily();

        $schedule->command('throw:order_auto_receive')->hourly();
        $schedule->command('throw:order_auto_cancel')->hourly();
        $schedule->command('throw:order_auto_rate')->dailyAt('23:30');
        $schedule->command('throw:order_count_auto_clear')->dailyAt('23:00');

        $schedule->command('recycle:order_auto_receive')->hourlyAt(05);
        $schedule->command('recycle:order_auto_cancel')->hourlyAt(10);
        $schedule->command('recycle:order_auto_rate')->dailyAt('23:45');
        $schedule->command('recycle:order_count_auto_clear')->dailyAt('23:00');
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
