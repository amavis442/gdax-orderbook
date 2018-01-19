<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Amavis442\Trading\Commands;

use Illuminate\Console\Command;
use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Bot\TickerBot;

/**
 * Description of RunTicker
 *
 * @author patrickteunissen
 */
class Ticker extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:ticker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the ticker.';

    protected $exchange;


    /**
     *
     */
    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("=== RUN [" . \Carbon\Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i:s') . "] ===");

        $bot = new TickerBot($this->exchange);

        while (1) {
            $bot->run();
            sleep(1);
        }

        $this->info("Exit ticker");
    }
}
