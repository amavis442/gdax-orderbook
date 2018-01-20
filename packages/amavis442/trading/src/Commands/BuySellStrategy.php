<?php

namespace Amavis442\Trading\Commands;

use Amavis442\Trading\Bot\OrderBot;
use Illuminate\Console\Command;
use Amavis442\Trading\Contracts\Exchange;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class BuySellStrategy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:buysellstrategy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buys and sells position depending on the given strategy.';

    protected $exchange;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;

        parent::__construct();
    }


    public function handle()
    {
        $orderBot = new OrderBot($this->exchange);
        $orderBot->run();
    }
}
