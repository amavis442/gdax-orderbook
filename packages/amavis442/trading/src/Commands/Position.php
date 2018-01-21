<?php

namespace Amavis442\Trading\Commands;

use Amavis442\Trading\Models\Setting;
use Illuminate\Console\Command;
use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Indicators\Stoploss;
use Amavis442\Trading\Bot\PositionBot;
use Illuminate\Support\Facades\Log;

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
        $settings = new Setting();

        $bot = new PositionBot($this->exchange);
        $bot->setStopLossIndicator(new Stoploss());

        while (1) {
            $botactive = ($settings->botactive == 1 ? true : false);
            if (!$botactive) {
                Log::warning("Bot is not active at the moment");
            } else {
                $bot->run($this->option('watch'));
            }
            sleep(2);
        }
    }
}
