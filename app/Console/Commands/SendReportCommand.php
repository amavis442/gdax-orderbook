<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a report to telegram';

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
        $ledger = (new \App\Services\WalletService())->getWallets();
        
        dispatch(new \App\Jobs\SendTelegramJob($ledger));
    }
}
