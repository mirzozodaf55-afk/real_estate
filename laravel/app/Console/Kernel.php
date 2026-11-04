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
        // $schedule->command('inspire')->hourly();
        // просроченные токены входа (config/sanctum.php → expiration) — удаляем из базы раз в сутки
        $schedule->command('sanctum:prune-expired --hours=24')->daily();

        // публичное демо: каждую ночь — к исходным данным, что бы ни натворили посетители за день
        if (\App\Support\Demo::enabled()) {
            $schedule->command('demo:reset --clean-files')->dailyAt('03:00');
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
