<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('app:run-valid-domains-checker-command')
        //     ->dailyAt('00:01')
        //     ->timezone('Asia/Dhaka');

        $schedule->command('app:run-logs-clear-command')
            ->hourly()
            ->timezone('Asia/Dhaka');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
