<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amavis442\Trading\Commands;


use Amavis442\Trading\Bot\OrderBot;
use Illuminate\Console\Command;
use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Strategies\Stoploss;
use Amavis442\Trading\Bot\PositionBot;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class Position extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:position {strategy=trailingstop : Which strategy to use} {--watch : Only update but do not place sell orders (sl)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the positionbot.';

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
        $bot = new PositionBot($this->exchange);

        $bot->setStopLossService(new Stoploss());

        while (1) {
            $bot->run($this->option('watch'));
            sleep(2);
        }
    }
}
