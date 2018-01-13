<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amavis442\Trading\Commands;


use Amavis442\Trading\Bot\OrderBot;
use Illuminate\Console\Command;
use Amavis442\Trading\Contracts\GdaxServiceInterface;
use Amavis442\Trading\Contracts\OrderServiceInterface;
use Amavis442\Trading\Contracts\PositionServiceInterface;

use Amavis442\Trading\Triggers\Stoploss;
use Amavis442\Trading\Bot\PositionBot;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class UpdatePositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:positions {strategy=trailingstop : Which strategy to use} {--watch : Only update but do not place sell orders (sl)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the positions.';

    protected $gdax;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GdaxServiceInterface $gdax)
    {
        $this->gdax = $gdax;

        parent::__construct();
    }


    public function handle()
    {
        $orderBot = new OrderBot($this->gdax);
        $bot = new PositionBot($this->gdax);

        $bot->setStopLossService(new Stoploss());

        while (1) {
            $orderBot->run();
            $bot->run($this->option('watch'));
            sleep(2);
        }
    }
}
