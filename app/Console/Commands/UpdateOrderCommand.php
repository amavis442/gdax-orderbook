<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\UpdateOrdersJob;
use App\Services\OrderService;

class UpdateOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update orders';

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
           dispatch(new \App\Jobs\UpdateOrdersJob(new OrderService()));
    }
}
