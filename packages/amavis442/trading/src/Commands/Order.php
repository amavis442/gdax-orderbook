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

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class Order extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the order.';

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
        $bot = new OrderBot($this->exchange);

        $bot->run();
    }
}
