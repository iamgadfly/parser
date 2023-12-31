<?php

namespace App\Console;

use App\Jobs\ParserJob;
use App\Jobs\RebagJob;
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
    protected function schedule(Schedule $schedule)
    {
        //$schedule->job(new ParserJob)->hourly();
        $schedule->job(new RebagJob)->everyMinute();
        //->everyMinute();
        //->hourly();
        // $schedule->job(new AddUrlJob)->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
