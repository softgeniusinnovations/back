<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
     
    protected $commands = [
        \App\Console\Commands\RemoveExpiredUserFromBounsAndTramcard::class,
        \App\Console\Commands\BirthdayGift::class,
        \App\Console\Commands\TransferAffiliateBalance::class,
    ];
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('expireuser:remove')->everyMinute();
        $schedule->command('birthday:gift')->everyMinute();
        $schedule->command('affiliate:transfer-balance')->dailyAt('00:01');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
