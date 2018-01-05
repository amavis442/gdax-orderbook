<?php

namespace App\Console;

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
        \App\Console\Commands\UpdateOrderCommand::class,
        \App\Console\Commands\CoinbaseCsvImport::class,
        \App\Console\Commands\UpdateTradesCommand::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /* $schedule->command('orders:update')
                  ->everyMinute();

        $schedule->command('trade:update')
                 ->everyThirtyMinutes();*/
        
         $schedule->command('report:send')
                 ->everyThirtyMinutes()->between('7:00', '22:00');
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
