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
        $schedule->call(function () {
            $comptesToBlock = \App\Models\Compte::where('statut', 'actif')
                ->whereNotNull('dateBlocage')
                ->whereDate('dateBlocage', now()->toDateString())
                ->get();

            foreach ($comptesToBlock as $compte) {
                \App\Jobs\BlockCompteJob::dispatch($compte->id);
            }
        })->dailyAt('00:00'); // Run daily at midnight

        // Schedule the check for expired blocks to run daily
        $schedule->call(function () {
            \App\Models\Compte::checkExpiredBlocks();
        })->daily();

        // Schedule the job to restore expired blocked accounts from Neon archive
        $schedule->job(new \App\Jobs\DebloquerCompteJob)->dailyAt('00:00');
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
