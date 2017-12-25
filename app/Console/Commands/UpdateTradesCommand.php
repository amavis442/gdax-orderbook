<?php

namespace App\Console\Commands;

use App\Jobs\GetTradesJob;
use App\Jobs\UpdateOrdersJob;
use Illuminate\Console\Command;

class UpdateTradesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trade:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the filled trades';

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
        dispatch(new GetTradesJob());
    }
}
